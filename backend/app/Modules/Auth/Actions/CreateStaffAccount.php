<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Auth\Models\UserRole;
use App\Shared\Actions\Action;
use App\Shared\Auth\Permission;
use App\Shared\Auth\Role;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Account of a staff member (organizer, committee) or of a superadmin. It has no
 * participant role and no profile; the conference role is assigned separately
 * (Conferences\AssignAdminToConference, BR8).
 *
 * Callers: the console command conferences:create-user (first superadmin, no actor)
 * and the superadmin team screen (actor required).
 */
final class CreateStaffAccount extends Action
{
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', PasswordRule::min(8)],
        ];
    }

    /**
     * @param  array{name:string, email:string, password:string}  $input
     * @param  Authenticatable|null  $actor  null only from the console
     */
    public function handle(array $input, ?Authenticatable $actor = null, bool $superadmin = false): User
    {
        if ($actor !== null) {
            // Only a superadmin has this permission without a conference.
            $this->authorize($actor, Permission::ConferencesAssignAdmins, null);
        }

        $input['email'] = Str::lower(trim((string) ($input['email'] ?? '')));
        $data = $this->validate($input);

        return DB::transaction(function () use ($data, $superadmin) {
            $user = User::query()->create($data);
            // The account is created by an administrator who knows the address.
            $user->forceFill(['email_verified_at' => now()])->save();

            if ($superadmin) {
                UserRole::query()->create(['user_id' => $user->id, 'role' => Role::Superadmin->value]);
            }

            return $user;
        });
    }
}
