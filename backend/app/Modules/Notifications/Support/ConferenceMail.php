<?php

namespace App\Modules\Notifications\Support;

use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * One mailable for all system e-mails; texts come from lang/en/mail.php (O33).
 */
final class ConferenceMail extends Mailable
{
    /**
     * @param  array<string, mixed>  $data
     * @param  list<Attachment>  $files
     */
    public function __construct(
        private readonly string $subjectLine,
        private readonly string $template,
        private readonly array $data,
        private readonly array $files = [],
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(view: 'notifications::mail.'.$this->template, with: $this->data);
    }

    /** @return list<Attachment> */
    public function attachments(): array
    {
        return $this->files;
    }
}
