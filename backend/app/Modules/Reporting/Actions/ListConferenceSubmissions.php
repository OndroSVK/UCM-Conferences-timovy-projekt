<?php

namespace App\Modules\Reporting\Actions;

use App\Modules\Submissions\Models\Submission;
use App\Modules\Workflow\Enums\SubmissionStatus;
use App\Shared\Actions\Action;
use App\Shared\Auth\Permission;
use App\Shared\Conference\ConferenceContext;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

/**
 * Admin / committee list of submissions of the ACTIVE conference (read-only, added for
 * the admin UI). Submissions that are not required are not listed.
 */
final class ListConferenceSubmissions extends Action
{
    public function __construct(private readonly ConferenceContext $context)
    {
    }

    public static function rules(): array
    {
        return ['status' => ['nullable', Rule::enum(SubmissionStatus::class)]];
    }

    /**
     * @param  array{status?:string|null}  $filters
     */
    public function handle(Authenticatable $actor, array $filters = []): LengthAwarePaginator
    {
        $conferenceId = $this->context->requireConferenceId();
        $this->authorize($actor, Permission::SubmissionsView, $conferenceId);
        $data = $this->validate($filters);

        $query = Submission::query()
            ->with(['registration.profile', 'latestVersion'])
            ->where('status', '!=', SubmissionStatus::NotRequired->value)
            ->orderByDesc('updated_at');

        if (! empty($data['status'])) {
            $query->where('status', $data['status']);
        }

        return $query->paginate(25)->withQueryString();
    }

    /**
     * Detail of one submission with versions and decisions.
     */
    public function find(Authenticatable $actor, int $submissionId): Submission
    {
        $conferenceId = $this->context->requireConferenceId();
        $this->authorize($actor, Permission::SubmissionsView, $conferenceId);

        return Submission::query()
            ->with(['registration.profile', 'versions', 'decisions'])
            ->findOrFail($submissionId);
    }
}
