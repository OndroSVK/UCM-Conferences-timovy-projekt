<?php

namespace App\Modules\Registration\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

final class RegistrationCreated implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly int $registrationId,
        public readonly int $conferenceId,
        public readonly int $userId,
        public readonly bool $newAccount,
    ) {
    }
}
