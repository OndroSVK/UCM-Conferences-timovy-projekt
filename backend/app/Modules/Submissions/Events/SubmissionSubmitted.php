<?php

namespace App\Modules\Submissions\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

final class SubmissionSubmitted implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly int $submissionId,
        public readonly int $versionId,
        public readonly int $version,
        public readonly int $registrationId,
        public readonly int $conferenceId,
    ) {
    }

    public function isNewVersion(): bool
    {
        return $this->version > 1;
    }
}
