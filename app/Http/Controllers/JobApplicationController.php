<?php

namespace App\Http\Controllers;

use App\Mail\TransactionalActionMail;
use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use RuntimeException;

class JobApplicationController extends Controller
{
    public function store(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        abort_unless($request->user()->role === 'professional', 403);
        abort_unless(
            $jobPosting->status === 'active' && $jobPosting->expires_at->toDateString() >= now()->toDateString(),
            403
        );

        $data = $request->validate([
            'presentation_message' => ['nullable', 'string', 'max:500'],
            'application_confirmation' => ['accepted'],
        ], [
            'application_confirmation.accepted' => 'Devi confermare l’invio della candidatura.',
            'presentation_message.max' => 'Il messaggio di presentazione non può superare 500 caratteri.',
        ]);

        [$application, $created] = [null, false];
        DB::transaction(function () use ($request, $jobPosting, $data, &$application, &$created): void {
            $application = JobApplication::firstOrCreate([
                'job_posting_id' => $jobPosting->id,
                'user_id' => $request->user()->id,
            ], [
                'status' => JobApplication::STATUS_RECEIVED,
                'presentation_message' => $data['presentation_message'] ?? null,
            ]);
            $created = $application->wasRecentlyCreated;

            if ($created) {
                $application->events()->create([
                    'actor_user_id' => $request->user()->id,
                    'type' => 'application_created',
                    'label' => 'Candidatura inviata',
                    'to_status' => JobApplication::STATUS_RECEIVED,
                ]);
            }
        });

        if ($created && $application !== null) {
            $jobPosting->loadMissing('owner');

            Mail::to($request->user()->email)->send(new TransactionalActionMail(
                mailSubject: 'Candidatura inviata: '.$jobPosting->title,
                heading: 'Candidatura inviata',
                intro: 'La tua candidatura è stata registrata correttamente.',
                actionLabel: 'Vedi le mie candidature',
                actionUrl: route('professional.applications.index'),
                details: [
                    'Annuncio: '.$jobPosting->title,
                    'Data invio: '.now()->format('d/m/Y H:i'),
                ],
            ));

            if ($jobPosting->owner?->email) {
                Mail::to($jobPosting->owner->email)->send(new TransactionalActionMail(
                    mailSubject: 'Nuova candidatura: '.$jobPosting->title,
                    heading: 'Hai ricevuto una nuova candidatura',
                    intro: 'Un professionista si è candidato al tuo annuncio.',
                    actionLabel: 'Apri la candidatura',
                    actionUrl: route('business.applications.show', $application),
                    details: [
                        'Annuncio: '.$jobPosting->title,
                        'Candidato: '.$request->user()->name,
                    ],
                ));
            }
        }

        return redirect()->route('dashboard')->with('status', 'Candidatura inviata. Annuncio aggiunto alla tua lista.');
    }

    public function updateStatus(Request $request, JobApplication $jobApplication): RedirectResponse
    {
        abort_unless($request->user()->role === 'business', 403);
        $jobApplication->loadMissing('jobPosting');
        abort_unless($jobApplication->jobPosting !== null && (int) $jobApplication->jobPosting->user_id === (int) $request->user()->id, 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(JobApplication::statusOptions()))],
        ]);

        $oldStatus = $jobApplication->status;

        DB::transaction(function () use ($jobApplication, $request, $data, $oldStatus): void {
            $updatedRows = JobApplication::query()->whereKey($jobApplication->getKey())->update(['status' => $data['status']]);
            if ($updatedRows !== 1) {
                throw new RuntimeException('Impossibile aggiornare lo stato della candidatura.');
            }

            if ($oldStatus !== $data['status']) {
                $jobApplication->events()->create([
                    'actor_user_id' => $request->user()->id,
                    'type' => 'status_changed',
                    'label' => 'Stato aggiornato a '.(JobApplication::statusOptions()[$data['status']] ?? $data['status']),
                    'from_status' => $oldStatus,
                    'to_status' => $data['status'],
                ]);
            }
        });

        return back()->with('status', 'Stato della candidatura aggiornato.')->with('status_variant', 'success');
    }
}
