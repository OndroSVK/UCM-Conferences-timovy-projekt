<?php

namespace App\Modules\Notifications\Actions;

use App\Modules\Notifications\Enums\EmailType;
use App\Modules\Notifications\Models\EmailLog;
use App\Shared\Actions\Action;

/**
 * Records every sending attempt: recipient, type, result (backend.md 4.6).
 */
final class LogEmail extends Action
{
    public function handle(
        int $conferenceId,
        ?int $registrationId,
        string $recipient,
        EmailType $type,
        bool $sent,
        ?string $error = null,
    ): EmailLog {
        return EmailLog::query()->create([
            'conference_id' => $conferenceId,
            'registration_id' => $registrationId,
            'recipient' => $recipient,
            'type' => $type,
            'result' => $sent ? EmailLog::SENT : EmailLog::FAILED,
            'error' => $error === null ? null : mb_substr($error, 0, 1000),
        ]);
    }
}
