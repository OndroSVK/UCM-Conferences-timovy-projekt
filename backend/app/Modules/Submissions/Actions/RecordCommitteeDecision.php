<?php

namespace App\Modules\Submissions\Actions;

use App\Modules\Submissions\Enums\DecisionType;
use App\Modules\Submissions\Events\CommitteeDecisionRecorded;
use App\Modules\Submissions\Models\Decision;
use App\Modules\Submissions\Models\Submission;
use App\Modules\Submissions\Models\SubmissionVersion;
use App\Modules\Workflow\Actions\ChangeStatus;
use App\Modules\Workflow\Enums\StatusCategory;
use App\Modules\Workflow\Enums\SubmissionStatus;
use App\Shared\Actions\Action;
use App\Shared\Actions\DomainException;
use App\Shared\Auth\Permission;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Single-round committee decision (O37): accepted / conditionally_accepted / rejected
 * with a justification for the author, stored in decisions for the latest version.
 * Organizers may not decide (R30).
 */
final class RecordCommitteeDecision extends Action
{
    public function __construct(private readonly ChangeStatus $changeStatus)
    {
    }

    public static function rules(): array
    {
        return [
            'decision' => ['required', Rule::enum(DecisionType::class)],
            'justification' => ['required', 'string', 'max:5000'],
        ];
    }

    /**
     * @param  array{decision:string, justification:string}  $input
     */
    public function handle(Authenticatable $actor, Submission $submission, array $input): Decision
    {
        $this->authorize($actor, Permission::SubmissionsDecide, $submission->conference_id);
        $data = $this->validate($input);
        $type = DecisionType::from($data['decision']);

        return DB::transaction(function () use ($actor, $submission, $data, $type) {
            $locked = Submission::query()->lockForUpdate()->findOrFail($submission->id);

            if ($locked->status !== SubmissionStatus::Submitted) {
                throw new DomainException(__('submissions.not_decidable'));
            }

            $version = SubmissionVersion::query()
                ->where('submission_id', $submission->id)
                ->orderByDesc('version')
                ->firstOrFail();

            $decision = Decision::query()->create([
                'conference_id' => $submission->conference_id,
                'submission_id' => $submission->id,
                'submission_version_id' => $version->id,
                'decision' => $type,
                'justification' => $data['justification'],
                'decided_by' => $actor->getAuthIdentifier(),
                'decided_at' => now(),
            ]);

            $this->changeStatus->handle($submission, StatusCategory::Submission, $type->status(), $actor);

            CommitteeDecisionRecorded::dispatch($decision->id, $submission->id, $submission->registration_id, $submission->conference_id);

            return $decision;
        });
    }
}
