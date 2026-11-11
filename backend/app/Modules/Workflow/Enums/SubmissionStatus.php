<?php

namespace App\Modules\Workflow\Enums;

/**
 * backend.md 7.3. The enum contains the whole expert process (R31); the prototype
 * allows only the single-round committee decision (O37).
 */
enum SubmissionStatus: string implements WorkflowStatus
{
    case Draft = 'draft';
    case NotRequired = 'not_required';
    case Submitted = 'submitted';
    /** Full version. */
    case AdministrativeCheck = 'administrative_check';
    /** Full version. */
    case UnderReview = 'under_review';
    case Accepted = 'accepted';
    case ConditionallyAccepted = 'conditionally_accepted';
    /** Full version. */
    case RevisionRequested = 'revision_requested';
    case Rejected = 'rejected';

    public static function transitions(): array
    {
        return [
            'draft' => ['submitted'],
            // submitted → submitted = new version before the deadline (PROPOSAL: O31)
            'submitted' => ['submitted', 'accepted', 'conditionally_accepted', 'rejected'],
        ];
    }

    public static function initialStates(): array
    {
        return [self::Draft, self::NotRequired];
    }
}
