<?php

namespace App\Shared\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

/**
 * Effective permissions of a user. Reads user_roles (owned by Auth) and
 * conference_user (owned by Conferences); writes nothing.
 *
 * A staff role and its extra permissions are valid only for the conference
 * they were assigned to (BR4, BR8).
 */
final class AccessControl
{
    public function hasGlobalRole(Authenticatable $user, Role $role): bool
    {
        return DB::table('user_roles')
            ->where('user_id', $user->getAuthIdentifier())
            ->where('role', $role->value)
            ->exists();
    }

    public function isSuperadmin(Authenticatable $user): bool
    {
        return $this->hasGlobalRole($user, Role::Superadmin);
    }

    public function isParticipant(Authenticatable $user): bool
    {
        return $this->hasGlobalRole($user, Role::Participant);
    }

    /** Superadmin or assigned to at least one conference. */
    public function isStaff(Authenticatable $user): bool
    {
        return $this->isSuperadmin($user)
            || DB::table('conference_user')->where('user_id', $user->getAuthIdentifier())->exists();
    }

    /**
     * @return list<int>
     */
    public function assignedConferenceIds(Authenticatable $user): array
    {
        return DB::table('conference_user')
            ->where('user_id', $user->getAuthIdentifier())
            ->distinct()
            ->pluck('conference_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function canAccessConference(Authenticatable $user, int $conferenceId): bool
    {
        return $this->isSuperadmin($user)
            || in_array($conferenceId, $this->assignedConferenceIds($user), true);
    }

    /**
     * @return list<Role>
     */
    public function conferenceRoles(Authenticatable $user, int $conferenceId): array
    {
        return DB::table('conference_user')
            ->where('user_id', $user->getAuthIdentifier())
            ->where('conference_id', $conferenceId)
            ->pluck('role')
            ->map(fn (string $role) => Role::from($role))
            ->all();
    }

    /**
     * @return list<Permission>
     */
    public function permissions(Authenticatable $user, ?int $conferenceId): array
    {
        if ($this->isSuperadmin($user)) {
            return Permission::cases();
        }

        if ($conferenceId === null) {
            return [];
        }

        $rows = DB::table('conference_user')
            ->where('user_id', $user->getAuthIdentifier())
            ->where('conference_id', $conferenceId)
            ->get(['role', 'extra_permissions']);

        $permissions = [];

        foreach ($rows as $row) {
            $role = Role::from($row->role);
            array_push($permissions, ...$role->permissions());

            // Delegated permissions are valid only on an organizer assignment.
            if ($role === Role::Organizer && $row->extra_permissions) {
                foreach (json_decode($row->extra_permissions, true) ?: [] as $value) {
                    $permission = Permission::tryFrom($value);
                    if ($permission?->isDelegable()) {
                        $permissions[] = $permission;
                    }
                }
            }
        }

        return array_values(array_unique($permissions, SORT_REGULAR));
    }

    public function can(Authenticatable $user, Permission $permission, ?int $conferenceId): bool
    {
        return in_array($permission, $this->permissions($user, $conferenceId), true);
    }
}
