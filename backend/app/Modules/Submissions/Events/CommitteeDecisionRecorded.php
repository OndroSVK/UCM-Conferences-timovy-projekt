<?php

namespace App\Modules\Submissions\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

final class CommitteeDecisionRecorded implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly int $decisionId,
        public readonly int $submissionId,
        public readonly int $registrationId,
        public readonly int $conferenceId,
    ) {
    }
}
