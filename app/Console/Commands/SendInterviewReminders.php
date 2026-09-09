<?php

namespace App\Console\Commands;

use App\Mail\TransactionalActionMail;
use App\Models\Interview;
use App\Models\JobApplicationEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendInterviewReminders extends Command
{
    protected $signature = 'interviews:send-reminders';

    protected $description = 'Send reminders for accepted interviews due within five hours.';

    public function handle(): int
    {
        $now = now();
        $deadline = $now->copy()->addHours(5);

        $interviews = Interview::query()
            ->with(['jobApplication.jobPosting.owner', 'jobApplication.professional'])
            ->where('status', Interview::STATUS_ACCEPTED)
            ->where('scheduled_at', '>', $now)
            ->where('scheduled_at', '<=', $deadline)
            ->whereDoesntHave('jobApplication.events', function ($query) {
                $query->where('type', 'interview_reminder_sent')
                    ->whereColumn('metadata->interview_id', 'interviews.id');
            })
            ->orderBy('scheduled_at')
            ->get();

        foreach ($interviews as $interview) {
            $application = $interview->jobApplication;
            $posting = $application?->jobPosting;
            $professional = $application?->professional;
            $business = $posting?->owner;

            if ($application === null || $posting === null) {
                continue;
            }

            $commonDetails = array_values(array_filter([
                'Annuncio: '.$posting->title,
                'Data: '.$interview->scheduled_at->format('d/m/Y H:i'),
                'Modalità: '.$interview->modeLabel(),
                $interview->location ? 'Sede / link: '.$interview->location : null,
            ]));

            if ($professional?->email) {
                Mail::to($professional->email)->send(new TransactionalActionMail(
                    mailSubject: 'Promemoria colloquio tra 5 ore: '.$posting->title,
                    heading: 'Il tuo colloquio è tra circa 5 ore',
                    intro: 'Ti ricordiamo il colloquio confermato con la struttura.',
                    actionLabel: 'Apri i colloqui',
                    actionUrl: route('interviews.index').'#interview-'.$interview->id,
                    details: array_values(array_filter([
                        ...$commonDetails,
                        $business?->email ? 'Email struttura: '.$business->email : null,
                        $business?->phone ? 'Telefono struttura: '.$business->phone : null,
                    ])),
                ));
            }

            if ($business?->email) {
                Mail::to($business->email)->send(new TransactionalActionMail(
                    mailSubject: 'Promemoria colloquio tra 5 ore: '.$posting->title,
                    heading: 'Il colloquio è tra circa 5 ore',
                    intro: 'Ti ricordiamo il colloquio confermato con il professionista.',
                    actionLabel: 'Apri la candidatura',
                    actionUrl: route('business.applications.show', $application),
                    details: array_values(array_filter([
                        ...$commonDetails,
                        $professional?->email ? 'Email professionista: '.$professional->email : null,
                        $professional?->phone ? 'Telefono professionista: '.$professional->phone : null,
                    ])),
                ));
            }

            JobApplicationEvent::create([
                'job_application_id' => $application->id,
                'actor_user_id' => null,
                'type' => 'interview_reminder_sent',
                'label' => 'Promemoria colloquio inviato a entrambe le parti',
                'metadata' => [
                    'interview_id' => $interview->id,
                    'scheduled_at' => $interview->scheduled_at->toIso8601String(),
                ],
            ]);
        }

        $this->info('Promemoria colloqui inviati: '.$interviews->count());

        return self::SUCCESS;
    }
}
