<?php

namespace App\Modules\Reporting\Actions;

use App\Modules\Registration\Models\Registration;
use App\Modules\Submissions\Models\Submission;
use App\Shared\Actions\Action;
use App\Shared\Auth\AccessControl;
use App\Shared\Auth\Permission;
use App\Shared\Conference\ConferenceContext;
use Illuminate\Contracts\Auth\Authenticatable;

/** Read-only counts for the active conference. Finance is never returned to the committee. */
final class GetConferenceOverview extends Action
{
    public function __construct(
        private readonly ConferenceContext $context,
        private readonly AccessControl $access,
    ) {}

    public function handle(Authenticatable $actor): array
    {
        $conferenceId = $this->context->requireConferenceId();
        $this->authorize($actor, Permission::RegistrationsView, $conferenceId);
        $this->authorize($actor, Permission::SubmissionsView, $conferenceId);

        $counts = [
            'registrations' => Registration::query()->count(),
            'confirmed' => Registration::query()->where('status', 'confirmed')->count(),
            'submissions' => Submission::query()->where('status', '!=', 'not_required')->count(),
            'awaiting_decision' => Submission::query()->where('status', 'submitted')->count(),
        ];

        if ($this->access->can($actor, Permission::RegistrationsViewFinance, $conferenceId)) {
            $counts['awaiting_payment'] = Registration::query()
                ->where('status', '!=', 'cancelled')
                ->whereIn('payment_status', ['pending', 'partially_paid'])->count();
        }

        return $counts;
    }
}
