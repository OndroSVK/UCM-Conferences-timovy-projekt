<?php

namespace App\Modules\Conferences\Actions;

use App\Modules\Conferences\Models\Conference;
use App\Shared\Actions\Action;
use App\Shared\Auth\AccessControl;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

/**
 * Conferences for the admin switcher: assigned ones (BR4), all for superadmin.
 * Newest first.
 */
final class ListAccessibleConferences extends Action
{
    public function __construct(private readonly AccessControl $access)
    {
    }

    /**
     * @return Collection<int, Conference>
     */
    public function handle(Authenticatable $actor): Collection
    {
        $query = Conference::query()->orderByDesc('starts_on');

        if (! $this->access->isSuperadmin($actor)) {
            $query->whereIn('id', $this->access->assignedConferenceIds($actor));
        }

        return $query->get();
    }
}
