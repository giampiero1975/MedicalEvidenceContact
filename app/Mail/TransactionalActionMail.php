<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransactionalActionMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<int, string>  $details
     */
    public function __construct(
        public readonly string $mailSubject,
        public readonly string $heading,
        public readonly string $intro,
        public readonly string $actionLabel,
        public readonly string $actionUrl,
        public readonly array $details = []
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject($this->mailSubject)
            ->view('emails.transactional-action');
    }
}
