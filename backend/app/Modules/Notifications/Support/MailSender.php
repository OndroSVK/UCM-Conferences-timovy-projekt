<?php

namespace App\Modules\Notifications\Support;

use App\Modules\Notifications\Actions\LogEmail;
use App\Modules\Notifications\Enums\EmailType;
use App\Modules\Registration\Models\Registration;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sends and logs. A failure is logged and reported but never thrown back, so it can
 * never undo a business operation (backend.md 4.6). Runs inside queued listeners.
 */
final class MailSender
{
    public function __construct(private readonly LogEmail $log)
    {
    }

    public function send(Registration $registration, EmailType $type, Mailable $mail): bool
    {
        $recipient = $registration->user()->firstOrFail()->email;

        try {
            Mail::to($recipient)->send($mail);
            $this->log->handle($registration->conference_id, $registration->id, $recipient, $type, true);

            return true;
        } catch (Throwable $e) {
            report($e);
            $this->log->handle($registration->conference_id, $registration->id, $recipient, $type, false, $e->getMessage());

            return false;
        }
    }
}
