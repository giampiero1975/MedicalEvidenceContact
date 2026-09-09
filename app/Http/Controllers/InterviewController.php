<?php

namespace App\Http\Controllers;

use App\Mail\TransactionalActionMail;
use App\Models\Interview;
use App\Models\JobApplication;
use App\Models\JobApplicationEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InterviewController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['business', 'professional'], true), 403);

        $activeInterviewStatuses = [
            Interview::STATUS_PROPOSED,
            Interview::STATUS_LEGACY_SCHEDULED,
            Interview::STATUS_REQUESTED,
            Interview::STATUS_ACCEPTED,
        ];

        $businessJobPostings = $user->role === 'business'
            ? $user->jobPostings()
                ->with([
                    'applications' => fn ($query) => $query
                        ->whereDoesntHave('interviews', fn ($interviews) => $interviews->whereIn('status', $activeInterviewStatuses))
                        ->with('professional:id,name,first_name,last_name,role,residence')
                        ->latest(),
                ])
                ->withCount([
                    'applications as applications_to_schedule_count' => fn ($query) => $query
                        ->whereDoesntHave('interviews', fn ($interviews) => $interviews->whereIn('status', $activeInterviewStatuses)),
                ])
                ->latest()
                ->get()
                ->filter(fn ($jobPosting) => $jobPosting->applications_to_schedule_count > 0)
                ->values()
            : collect();

        $professionalApplications = $user->role === 'professional'
            ? $user->jobApplications()
                ->with('jobPosting.owner.businessProfile')
                ->latest()
                ->get()
            : collect();

        $interviews = Interview::query()
            ->with(['jobApplication.jobPosting.owner.businessProfile', 'jobApplication.professional'])
            ->when($user->role === 'business', fn ($query) => $query->where('business_user_id', $user->id))
            ->when(
                $user->role === 'professional',
                fn ($query) => $query->whereHas(
                    'jobApplication',
                    fn ($applications) => $applications->where('user_id', $user->id)
                )
            )
            ->orderBy('scheduled_at')
            ->get();

        return view('interviews.index', [
            'businessJobPostings' => $businessJobPostings,
            'professionalApplications' => $professionalApplications,
            'interviews' => $interviews,
            'role' => $user->role,
        ]);
    }

    public function store(Request $request, JobApplication $jobApplication): RedirectResponse
    {
        abort_unless($request->user()->role === 'business', 403);
        $jobApplication->loadMissing('jobPosting', 'professional');
        abort_unless(
            $jobApplication->jobPosting !== null
            && (int) $jobApplication->jobPosting->user_id === (int) $request->user()->id,
            403
        );

        if ($jobApplication->interviews()->whereIn('status', [Interview::STATUS_REQUESTED, Interview::STATUS_ACCEPTED])->exists()) {
            return back()
                ->withErrors(['interview' => 'Esiste già uno slot selezionato o un colloquio confermato per questa candidatura.'])
                ->withInput();
        }

        $data = $request->validate([
            'scheduled_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['required', 'integer', Rule::in([15, 30, 45, 60, 90])],
            'mode' => ['required', Rule::in(['in_person', 'video', 'phone'])],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($data['mode'] !== 'phone' && blank($data['location'] ?? null)) {
            return back()->withErrors(['location' => 'Indica la sede oppure il link del colloquio.'])->withInput();
        }

        $interview = DB::transaction(function () use ($jobApplication, $request, $data): Interview {
            $interview = $jobApplication->interviews()->create([
                ...$data,
                'business_user_id' => $request->user()->id,
                'status' => Interview::STATUS_PROPOSED,
            ]);

            $previousStatus = $jobApplication->status;
            if ($jobApplication->status !== JobApplication::STATUS_INTERVIEW_SCHEDULED) {
                $jobApplication->update(['status' => JobApplication::STATUS_INTERVIEW_SCHEDULED]);
            }

            JobApplicationEvent::create([
                'job_application_id' => $jobApplication->id,
                'actor_user_id' => $request->user()->id,
                'type' => 'interview_slot_proposed',
                'label' => 'Slot colloquio proposto per '.$interview->scheduled_at->format('d/m/Y H:i'),
                'from_status' => $previousStatus,
                'to_status' => JobApplication::STATUS_INTERVIEW_SCHEDULED,
                'metadata' => [
                    'interview_id' => $interview->id,
                    'mode' => $interview->mode,
                    'duration_minutes' => $interview->duration_minutes,
                ],
            ]);

            return $interview;
        });

        if ($jobApplication->professional?->email) {
            Mail::to($jobApplication->professional->email)->send(new TransactionalActionMail(
                mailSubject: 'Nuovo slot colloquio: '.$jobApplication->jobPosting->title,
                heading: 'La struttura ha proposto uno slot di colloquio',
                intro: 'Apri la sezione colloqui per vedere gli slot disponibili e scegliere quello adatto a te.',
                actionLabel: 'Visualizza gli slot',
                actionUrl: route('interviews.index'),
                details: [
                    'Annuncio: '.$jobApplication->jobPosting->title,
                    'Data: '.$interview->scheduled_at->format('d/m/Y H:i'),
                    'Modalità: '.$interview->modeLabel(),
                ],
            ));
        }

        return back()->with('status', 'Slot colloquio proposto. Puoi aggiungerne altri finché il professionista non ne seleziona uno.')->with('status_variant', 'success');
    }

    public function respond(Request $request, Interview $interview): RedirectResponse
    {
        abort_unless($request->user()->role === 'professional', 403);

        $interview->loadMissing('jobApplication.jobPosting.owner');
        abort_unless(
            $interview->jobApplication !== null
            && (int) $interview->jobApplication->user_id === (int) $request->user()->id,
            403
        );

        if (! $interview->isAvailableProposal()) {
            return back()->withErrors([
                'response' => 'Questo slot non è più disponibile.',
            ]);
        }

        if ($interview->jobApplication->interviews()
            ->whereKeyNot($interview->getKey())
            ->whereIn('status', [Interview::STATUS_REQUESTED, Interview::STATUS_ACCEPTED])
            ->exists()) {
            return back()->withErrors([
                'response' => 'Hai già selezionato uno slot per questa candidatura.',
            ]);
        }

        $request->validate([
            'contact_sharing_consent' => ['accepted'],
        ], [
            'contact_sharing_consent.accepted' => 'Per richiedere il colloquio devi autorizzare la condivisione dei contatti in caso di conferma finale.',
        ]);

        $interview->update([
            'status' => Interview::STATUS_REQUESTED,
            'contact_sharing_consent' => true,
            'responded_at' => now(),
        ]);

        JobApplicationEvent::create([
            'job_application_id' => $interview->job_application_id,
            'actor_user_id' => $request->user()->id,
            'type' => 'interview_slot_requested',
            'label' => 'Slot selezionato dal professionista: '.$interview->scheduled_at->format('d/m/Y H:i'),
            'metadata' => [
                'interview_id' => $interview->id,
                'contact_sharing_consent' => true,
            ],
        ]);

        $business = $interview->jobApplication->jobPosting?->owner;
        if ($business?->email) {
            Mail::to($business->email)->send(new TransactionalActionMail(
                mailSubject: 'Richiesta colloquio: '.$interview->jobApplication->jobPosting->title,
                heading: 'Il professionista ha selezionato uno slot',
                intro: 'Conferma o rifiuta lo slot richiesto dalla scheda candidatura.',
                actionLabel: 'Apri la candidatura',
                actionUrl: route('business.applications.show', $interview->jobApplication),
                details: [
                    'Annuncio: '.$interview->jobApplication->jobPosting->title,
                    'Professionista: '.$request->user()->name,
                    'Data: '.$interview->scheduled_at->format('d/m/Y H:i'),
                ],
            ));
        }

        return back()
            ->with('status', 'Slot selezionato. La struttura deve ora confermare il colloquio.')
            ->with('status_variant', 'success');
    }

    public function confirm(Request $request, Interview $interview): RedirectResponse
    {
        abort_unless($request->user()->role === 'business', 403);

        $interview->loadMissing('jobApplication.jobPosting', 'jobApplication.professional');
        abort_unless(
            $interview->jobApplication?->jobPosting !== null
            && (int) $interview->jobApplication->jobPosting->user_id === (int) $request->user()->id,
            403
        );

        if ($interview->status !== Interview::STATUS_REQUESTED) {
            return back()->withErrors(['interview' => 'Questo slot non è in attesa di conferma.']);
        }

        $data = $request->validate([
            'decision' => ['required', Rule::in(['accepted', 'declined'])],
        ]);

        DB::transaction(function () use ($interview, $data): void {
            $interview->update(['status' => $data['decision']]);

            if ($data['decision'] === Interview::STATUS_ACCEPTED) {
                $interview->jobApplication->interviews()
                    ->whereKeyNot($interview->getKey())
                    ->whereIn('status', [Interview::STATUS_PROPOSED, Interview::STATUS_LEGACY_SCHEDULED])
                    ->update(['status' => Interview::STATUS_CANCELLED]);
            }

            JobApplicationEvent::create([
                'job_application_id' => $interview->job_application_id,
                'actor_user_id' => request()->user()->id,
                'type' => 'interview_business_decision',
                'label' => $data['decision'] === Interview::STATUS_ACCEPTED
                    ? 'Colloquio confermato dalla struttura'
                    : 'Slot rifiutato dalla struttura',
                'metadata' => [
                    'interview_id' => $interview->id,
                    'decision' => $data['decision'],
                ],
            ]);
        });

        $professional = $interview->jobApplication->professional;
        $posting = $interview->jobApplication->jobPosting;

        if ($data['decision'] === Interview::STATUS_ACCEPTED) {
            if ($professional?->email) {
                Mail::to($professional->email)->send(new TransactionalActionMail(
                    mailSubject: 'Colloquio confermato: '.$posting->title,
                    heading: 'La struttura ha confermato il colloquio',
                    intro: 'Il colloquio è ora confermato da entrambe le parti.',
                    actionLabel: 'Apri i colloqui',
                    actionUrl: route('interviews.index'),
                    details: array_values(array_filter([
                        'Data: '.$interview->scheduled_at->format('d/m/Y H:i'),
                        'Modalità: '.$interview->modeLabel(),
                        $request->user()->email ? 'Email struttura: '.$request->user()->email : null,
                        $request->user()->phone ? 'Telefono struttura: '.$request->user()->phone : null,
                    ])),
                ));
            }

            if ($request->user()->email) {
                Mail::to($request->user()->email)->send(new TransactionalActionMail(
                    mailSubject: 'Colloquio confermato: '.$posting->title,
                    heading: 'Colloquio confermato',
                    intro: 'Il colloquio è stato confermato e, con il consenso del professionista, i contatti sono ora disponibili.',
                    actionLabel: 'Apri la candidatura',
                    actionUrl: route('business.applications.show', $interview->jobApplication),
                    details: array_values(array_filter([
                        'Professionista: '.$professional?->name,
                        'Data: '.$interview->scheduled_at->format('d/m/Y H:i'),
                        $professional?->email ? 'Email professionista: '.$professional->email : null,
                        $professional?->phone ? 'Telefono professionista: '.$professional->phone : null,
                    ])),
                ));
            }
        } elseif ($professional?->email) {
            Mail::to($professional->email)->send(new TransactionalActionMail(
                mailSubject: 'Slot colloquio non confermato: '.$posting->title,
                heading: 'La struttura non ha confermato lo slot scelto',
                intro: 'Puoi tornare nella sezione colloqui e scegliere un altro slot disponibile.',
                actionLabel: 'Scegli un altro slot',
                actionUrl: route('interviews.index'),
                details: [
                    'Data rifiutata: '.$interview->scheduled_at->format('d/m/Y H:i'),
                ],
            ));
        }

        return back()
            ->with('status', $data['decision'] === Interview::STATUS_ACCEPTED ? 'Colloquio confermato.' : 'Slot rifiutato. Il professionista può sceglierne un altro.')
            ->with('status_variant', 'success');
    }

    public function cancel(Request $request, Interview $interview): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, ['business', 'professional'], true), 403);

        $interview->loadMissing('jobApplication.jobPosting.owner', 'jobApplication.professional');

        $application = $interview->jobApplication;
        $posting = $application?->jobPosting;
        abort_unless($application !== null && $posting !== null, 404);

        $isBusinessOwner = $request->user()->role === 'business'
            && (int) $posting->user_id === (int) $request->user()->id;
        $isProfessionalOwner = $request->user()->role === 'professional'
            && (int) $application->user_id === (int) $request->user()->id;

        abort_unless($isBusinessOwner || $isProfessionalOwner, 403);

        if ($interview->status !== Interview::STATUS_ACCEPTED) {
            return back()->withErrors(['interview' => 'Puoi annullare solo un colloquio già confermato.']);
        }

        $data = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $reason = trim((string) ($data['cancellation_reason'] ?? ''));

        DB::transaction(function () use ($interview, $request, $reason): void {
            $interview->update(['status' => Interview::STATUS_CANCELLED]);

            JobApplicationEvent::create([
                'job_application_id' => $interview->job_application_id,
                'actor_user_id' => $request->user()->id,
                'type' => 'interview_cancelled',
                'label' => 'Colloquio annullato da '.($request->user()->role === 'business' ? 'struttura' : 'professionista'),
                'metadata' => [
                    'interview_id' => $interview->id,
                    'cancelled_by_role' => $request->user()->role,
                    'reason' => $reason !== '' ? $reason : null,
                ],
            ]);
        });

        $details = array_values(array_filter([
            'Annuncio: '.$posting->title,
            'Data: '.$interview->scheduled_at->format('d/m/Y H:i'),
            $reason !== '' ? 'Motivo: '.$reason : null,
        ]));

        $professional = $application->professional;
        $business = $posting->owner;

        if ($professional?->email) {
            Mail::to($professional->email)->send(new TransactionalActionMail(
                mailSubject: 'Colloquio annullato: '.$posting->title,
                heading: 'Il colloquio è stato annullato',
                intro: 'Puoi tornare nella sezione colloqui per verificare lo stato e riprogrammare un nuovo appuntamento.',
                actionLabel: 'Gestisci colloqui',
                actionUrl: route('interviews.index'),
                details: $details,
            ));
        }

        if ($business?->email) {
            Mail::to($business->email)->send(new TransactionalActionMail(
                mailSubject: 'Colloquio annullato: '.$posting->title,
                heading: 'Il colloquio è stato annullato',
                intro: 'Puoi tornare nella sezione colloqui per verificare lo stato e riprogrammare un nuovo appuntamento.',
                actionLabel: 'Gestisci colloqui',
                actionUrl: route('interviews.index'),
                details: $details,
            ));
        }

        return back()
            ->with('status', 'Colloquio annullato. Entrambe le parti sono state notificate.')
            ->with('status_variant', 'success');
    }
}
