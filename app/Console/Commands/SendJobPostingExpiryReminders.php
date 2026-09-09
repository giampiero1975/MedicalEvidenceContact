<?php

namespace App\Console\Commands;

use App\Mail\TransactionalActionMail;
use App\Models\JobPosting;
use App\Services\TransactionalNotificationDispatcher;
use Illuminate\Console\Command;

class SendJobPostingExpiryReminders extends Command
{
    protected $signature = 'job-postings:send-expiry-reminders';

    protected $description = 'Send business reminders for active job postings expiring in seven days.';

    public function handle(): int
    {
        $targetDate = now()->addDays(7)->toDateString();
        $sentCount = 0;
        $dispatcher = app(TransactionalNotificationDispatcher::class);

        $postings = JobPosting::query()
            ->with('owner')
            ->where('status', 'active')
            ->whereDate('expires_at', $targetDate)
            ->whereNull('expiry_reminder_sent_at')
            ->orderBy('id')
            ->get();

        foreach ($postings as $posting) {
            $business = $posting->owner;

            if (! $business) {
                continue;
            }

            $handled = $dispatcher->dispatch(
                $business,
                'job_postings',
                new TransactionalActionMail(
                    mailSubject: 'Annuncio in scadenza tra 7 giorni: '.$posting->title,
                    heading: 'Il tuo annuncio scade tra 7 giorni',
                    intro: 'Controlla l’annuncio e, se necessario, estendi la data di scadenza prima che non sia più visibile ai professionisti.',
                    actionLabel: 'Gestisci annuncio',
                    actionUrl: route('job-postings.edit', $posting),
                    details: [
                        'Annuncio: '.$posting->title,
                        'Scadenza: '.$posting->expires_at->format('d/m/Y'),
                        'Località: '.$posting->workplace_address,
                    ],
                )
            );

            if (! $handled) {
                continue;
            }

            $posting->forceFill(['expiry_reminder_sent_at' => now()])->save();
            $sentCount++;
        }

        $this->info('Promemoria scadenza annunci gestiti: '.$sentCount);

        return self::SUCCESS;
    }
}
