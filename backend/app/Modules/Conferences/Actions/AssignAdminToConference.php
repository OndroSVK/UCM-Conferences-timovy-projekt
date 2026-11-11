<?php

namespace App\Modules\Conferences\Actions;

use App\Modules\Conferences\Models\ConferenceAssignment;
use App\Modules\Conferences\Models\Conference;
use App\Shared\Actions\Action;
use App\Shared\Actions\DomainException;
use App\Shared\Auth\Permission;
use App\Shared\Auth\Role;
use App\Shared\Conference\ConferenceContext;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Superadmin assigns or removes a staff role of a user in one conference (BR4, BR8).
 * Delegated permissions (payments.record, payments.waive, invoices.retry) are allowed
 * only for an organizer and are valid only in that conference.
 *
 * PROPOSAL: BO8 — only superadmin assigns.
 */
final class AssignAdminToConference extends Action
{
    public function __construct(private readonly ConferenceContext $context)
    {
    }

    public static function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['required', Rule::in(array_map(fn (Role $r) => $r->value, Role::assignableInPrototype()))],
            'extra_permissions' => ['array'],
            'extra_permissions.*' => [Rule::in(array_map(fn (Permission $p) => $p->value, Permission::delegable()))],
        ];
    }

    /**
     * @param  array{user_id:int, role:string, extra_permissions?:list<string>}  $input
     */
    public function handle(Authenticatable $actor, Conference $conference, array $input): ConferenceAssignment
    {
        $this->authorize($actor, Permission::ConferencesAssignAdmins, $conference->id);
        $data = $this->validate($input);

        $role = Role::from($data['role']);
        $extra = array_values(array_unique($data['extra_permissions'] ?? []));

        if ($extra !== [] && $role !== Role::Organizer) {
            throw new DomainException(__('errors.delegation_only_organizer'));
        }

        return $this->context->runForConference($conference->id, fn () => DB::transaction(
            fn () => ConferenceAssignment::query()->updateOrCreate(
                ['conference_id' => $conference->id, 'user_id' => $data['user_id'], 'role' => $role->value],
                ['extra_permissions' => $extra ?: null, 'assigned_by' => $actor->getAuthIdentifier()],
            ),
        ));
    }

    /**
     * Current staff of a conference (for the superadmin team page).
     *
     * @return \Illuminate\Support\Collection<int, ConferenceAssignment>
     */
    public function assignments(Authenticatable $actor, Conference $conference): \Illuminate\Support\Collection
    {
        $this->authorize($actor, Permission::ConferencesAssignAdmins, $conference->id);

        return $this->context->runForConference($conference->id, fn () => ConferenceAssignment::query()
            ->with('user')
            ->orderBy('role')
            ->get());
    }

    public function revoke(Authenticatable $actor, Conference $conference, int $userId, Role $role): void
    {
        $this->authorize($actor, Permission::ConferencesAssignAdmins, $conference->id);

        $this->context->runForConference($conference->id, fn () => DB::transaction(
            fn () => ConferenceAssignment::query()
                ->where('user_id', $userId)
                ->where('role', $role->value)
                ->get()
                ->each->delete(),
        ));
    }
}
