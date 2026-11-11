<?php

namespace App\Modules\Submissions\Actions;

use App\Modules\Submissions\Events\SubmissionSubmitted;
use App\Modules\Submissions\Models\Submission;
use App\Modules\Submissions\Models\SubmissionVersion;
use App\Modules\Submissions\Support\PdfUpload;
use App\Modules\Workflow\Actions\ChangeStatus;
use App\Modules\Workflow\Enums\StatusCategory;
use App\Modules\Workflow\Enums\SubmissionStatus;
use App\Shared\Actions\Action;
use App\Shared\Actions\DomainException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

/**
 * New version of the file before the deadline; old versions stay.
 *
 * PROPOSAL: O31 — allowed only while the submission is "submitted" (before the committee
 * decides); the status stays / returns to "submitted" (recorded in the history).
 */
final class ReplaceSubmissionFile extends Action
{
    public function __construct(
        private readonly PdfUpload $pdf,
        private readonly ChangeStatus $changeStatus,
    ) {
    }

    /**
     * @param  array{file:mixed}  $input
     */
    public function handle(Authenticatable $actor, Submission $submission, array $input): SubmissionVersion
    {
        $registration = $submission->registration()->firstOrFail();
        $this->authorizeOwner($actor, $registration->user_id);

        $file = $this->pdf->validate($input['file'] ?? null);

        if ($submission->status !== SubmissionStatus::Submitted) {
            throw new DomainException(__('submissions.cannot_replace'));
        }

        if (! $submission->conference()->firstOrFail()->isSubmissionOpen()) {
            throw new DomainException(__('submissions.deadline_passed'));
        }

        return DB::transaction(function () use ($actor, $submission, $file) {
            $next = (int) SubmissionVersion::query()
                ->where('submission_id', $submission->id)
                ->lockForUpdate()
                ->max('version') + 1;

            $version = $this->pdf->storeVersion($submission, $file, $next, $actor);

            $this->changeStatus->handle(
                $submission,
                StatusCategory::Submission,
                SubmissionStatus::Submitted,
                $actor,
                "New version {$next}",
            );

            SubmissionSubmitted::dispatch($submission->id, $version->id, $next, $submission->registration_id, $submission->conference_id);

            return $version;
        });
    }
}
