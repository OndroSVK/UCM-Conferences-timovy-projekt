<?php

namespace App\Modules\Workflow\Actions;

use App\Modules\Registration\Models\Registration;
use App\Modules\Workflow\Enums\StatusCategory;
use App\Modules\Workflow\Models\StatusHistory;
use App\Shared\Actions\Action;
use App\Shared\Auth\AccessControl;
use App\Shared\Auth\Permission;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

/**
 * Status history of a registration, filtered by permission: the owner sees all own
 * entries; staff sees payment entries only with the finance permission (the programme
 * committee does not), registration and submission entries with registrations.view.
 */
final class GetStatusHistory extends Action
{
    public function __construct(private readonly AccessControl $access)
    {
    }

    /**
     * @return Collection<int, StatusHistory>
     */
    public function handle(Authenticatable $actor, Registration $registration): Collection
    {
        $categories = $this->visibleCategories($actor, $registration);

        if ($categories === []) {
            throw new AuthorizationException(__('errors.forbidden'));
        }

        return StatusHistory::query()
            ->where('registration_id', $registration->id)
            ->whereIn('category', array_map(fn (StatusCategory $c) => $c->value, $categories))
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return list<StatusCategory>
     */
    private function visibleCategories(Authenticatable $actor, Registration $registration): array
    {
        if ((int) $actor->getAuthIdentifier() === $registration->user_id) {
            return StatusCategory::cases();
        }

        $conferenceId = $registration->conference_id;
        $categories = [];

        if ($this->access->can($actor, Permission::RegistrationsView, $conferenceId)) {
            $categories[] = StatusCategory::Registration;
            $categories[] = StatusCategory::Submission;
        }

        if ($this->access->can($actor, Permission::RegistrationsViewFinance, $conferenceId)) {
            $categories[] = StatusCategory::Payment;
        }

        return $categories;
    }
}
