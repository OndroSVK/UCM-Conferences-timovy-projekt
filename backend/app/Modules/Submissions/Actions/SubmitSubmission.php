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
 * The author uploads the PDF (abstract for a speaker, poster for a poster author):
 * version 1, draft → submitted, SubmissionSubmitted. Only before submission_deadline.
 */
final class SubmitSubmission extends Action
{
    public function __construct(
        private readonly PdfUpload $pdf,
        private readonly ChangeStatus $changeStatus,
    ) {
    }

    public static function rules(): array
    {
        return ['title' => ['required', 'string', 'max:300']];
    }

    /**
     * @param  array{title:string, file:mixed}  $input
     */
    public function handle(Authenticatable $actor, Submission $submission, array $input): SubmissionVersion
    {
        $registration = $submission->registration()->firstOrFail();
        $this->authorizeOwner($actor, $registration->user_id);

        $data = $this->validate($input);
        $file = $this->pdf->validate($input['file'] ?? null);

        if ($submission->status !== SubmissionStatus::Draft) {
            throw new DomainException(__('submissions.already_submitted'));
        }

        if (! $submission->conference()->firstOrFail()->isSubmissionOpen()) {
            throw new DomainException(__('submissions.deadline_passed'));
        }

        return DB::transaction(function () use ($actor, $submission, $data, $file) {
            $submission->forceFill(['title' => $data['title']])->save();
            $version = $this->pdf->storeVersion($submission, $file, 1, $actor);

            $this->changeStatus->handle($submission, StatusCategory::Submission, SubmissionStatus::Submitted, $actor);

            SubmissionSubmitted::dispatch($submission->id, $version->id, 1, $submission->registration_id, $submission->conference_id);

            return $version;
        });
    }
}
