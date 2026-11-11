<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Auth\Models\UserRole;
use App\Shared\Actions\Action;
use App\Shared\Auth\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Creates a participant account. Called by Registration\RegisterNewParticipant inside
 * its transaction, so that Registration never writes the users table itself (backend.md 3.3).
 */
final class CreateUserAccount extends Action
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
     */
    public function handle(array $input): User
    {
        $input['email'] = Str::lower(trim((string) ($input['email'] ?? '')));
        $data = $this->validate($input);

        return DB::transaction(function () use ($data) {
            $user = User::query()->create($data);
            UserRole::query()->create(['user_id' => $user->id, 'role' => Role::Participant->value]);

            return $user;
        });
    }

    /** Adds the participant role to an existing account (e.g. a staff member registering). */
    public function ensureParticipantRole(User $user): void
    {
        UserRole::query()->firstOrCreate(['user_id' => $user->id, 'role' => Role::Participant->value]);
    }
}
