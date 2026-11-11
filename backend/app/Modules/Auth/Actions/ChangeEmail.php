<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Models\User;
use App\Shared\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Changes the login e-mail; the new address must be verified again.
 * Called by ParticipantPortal\UpdateMyProfile.
 */
final class ChangeEmail extends Action
{
    public function handle(User $user, string $email): void
    {
        $email = Str::lower(trim($email));

        if ($email === $user->email) {
            return;
        }

        $this->validate(['email' => $email], [
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        DB::transaction(fn () => $user->forceFill(['email' => $email, 'email_verified_at' => null])->save());

        $user->sendEmailVerificationNotification();
    }
}
