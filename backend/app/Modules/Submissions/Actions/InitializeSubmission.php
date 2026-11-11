<?php

namespace App\Modules\Submissions\Actions;

use App\Modules\Conferences\Enums\SubmissionRequirement;
use App\Modules\Registration\Models\Registration;
use App\Modules\Submissions\Enums\SubmissionType;
use App\Modules\Submissions\Models\Submission;
use App\Modules\Workflow\Actions\ChangeStatus;
use App\Modules\Workflow\Enums\StatusCategory;
use App\Modules\Workflow\Enums\SubmissionStatus;
use App\Shared\Actions\Action;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Creates the submission record of a new registration with the initial status by
 * category (backend.md 7.4): speaker → draft (abstract), poster author → draft (poster),
 * passive participant → not_required. Called by Registration\CreateRegistration.
 *
 * PROPOSAL: the submission status lives only in submissions (not duplicated in registrations).
 */
final class InitializeSubmission extends Action
{
    public function __construct(private readonly ChangeStatus $changeStatus)
    {
    }

    public function handle(Registration $registration, SubmissionRequirement $requirement, ?Authenticatable $actor = null): Submission
    {
        $type = SubmissionType::fromRequirement($requirement);

        $submission = Submission::query()->create([
            'conference_id' => $registration->conference_id,
            'registration_id' => $registration->id,
            'type' => $type,
        ]);

        $this->changeStatus->initialize(
            $submission,
            StatusCategory::Submission,
            $type === null ? SubmissionStatus::NotRequired : SubmissionStatus::Draft,
            $actor,
        );

        return $submission;
    }
}
