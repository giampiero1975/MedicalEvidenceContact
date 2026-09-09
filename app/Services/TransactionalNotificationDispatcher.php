<?php

namespace App\Services;

use App\Mail\TransactionalActionMail;
use App\Models\NotificationEvent;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class TransactionalNotificationDispatcher
{
    public function dispatch(User $user, string $category, TransactionalActionMail $mail): bool
    {
        $preference = $user->notificationPreferences()
            ->where('category', $category)
            ->first();

        $enabled = $preference?->enabled ?? true;
        $frequency = $preference?->frequency ?? NotificationPreference::FREQUENCY_IMMEDIATE;

        if (! $enabled || blank($user->email)) {
            return false;
        }

        if ($frequency === NotificationPreference::FREQUENCY_IMMEDIATE) {
            Mail::to($user->email)->send($mail);

            return true;
        }

        NotificationEvent::create([
            'user_id' => $user->id,
            'category' => $category,
            'frequency' => $frequency,
            'subject' => $mail->mailSubject,
            'heading' => $mail->heading,
            'intro' => $mail->intro,
            'action_label' => $mail->actionLabel,
            'action_url' => $mail->actionUrl,
            'details' => $mail->details,
            'secondary_action_label' => $mail->secondaryActionLabel,
            'secondary_action_url' => $mail->secondaryActionUrl,
            'occurred_at' => now(),
        ]);

        return true;
    }
}
