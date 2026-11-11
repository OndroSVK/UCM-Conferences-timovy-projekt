<?php

namespace App\Shared\Actions;

use App\Shared\Auth\AccessControl;
use App\Shared\Auth\Permission;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Validator;

/**
 * Base class of every feature (BR2, backend.md 3.4).
 *
 * A feature checks the permission AND the conference, runs in a transaction,
 * changes statuses only through Workflow\Actions\ChangeStatus and raises
 * events that are dispatched after commit.
 */
abstract class Action
{
    /**
     * Validation rules of the input; empty when the feature takes no free input.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    /**
     * @throws AuthorizationException
     */
    protected function authorize(Authenticatable $actor, Permission $permission, ?int $conferenceId): void
    {
        if (! app(AccessControl::class)->can($actor, $permission, $conferenceId)) {
            throw new AuthorizationException(__('errors.forbidden'));
        }
    }

    /**
     * Participant features: the record must belong to the actor.
     *
     * @throws AuthorizationException
     */
    protected function authorizeOwner(Authenticatable $actor, int $ownerUserId): void
    {
        if ((int) $actor->getAuthIdentifier() !== $ownerUserId) {
            throw new AuthorizationException(__('errors.forbidden'));
        }
    }

    /**
     * Owner of the record, or a staff member with the permission in the record's conference.
     *
     * @throws AuthorizationException
     */
    protected function authorizeOwnerOr(Authenticatable $actor, int $ownerUserId, Permission $permission, int $conferenceId): void
    {
        if ((int) $actor->getAuthIdentifier() === $ownerUserId) {
            return;
        }

        $this->authorize($actor, $permission, $conferenceId);
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>|null  $rules
     * @return array<string, mixed>
     */
    protected function validate(array $input, ?array $rules = null): array
    {
        return Validator::make($input, $rules ?? static::rules())->validate();
    }
}
