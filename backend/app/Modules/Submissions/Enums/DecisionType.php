<?php

namespace App\Modules\Submissions\Enums;

use App\Modules\Workflow\Enums\SubmissionStatus;

/** Single-round committee decision in the prototype (O37). */
enum DecisionType: string
{
    case Accepted = 'accepted';
    case ConditionallyAccepted = 'conditionally_accepted';
    case Rejected = 'rejected';

    public function status(): SubmissionStatus
    {
        return match ($this) {
            self::Accepted => SubmissionStatus::Accepted,
            self::ConditionallyAccepted => SubmissionStatus::ConditionallyAccepted,
            self::Rejected => SubmissionStatus::Rejected,
        };
    }
}
