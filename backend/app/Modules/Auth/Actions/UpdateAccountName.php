<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Models\User;
use App\Shared\Actions\Action;

/**
 * Keeps the display name of the account in sync with the participant profile.
 * Called by ParticipantPortal\UpdateMyProfile, so only Auth writes the users table.
 */
final class UpdateAccountName extends Action
{
    public function handle(User $user, string $name): void
    {
        $data = $this->validate(['name' => $name], ['name' => ['required', 'string', 'max:255']]);

        if ($user->name !== $data['name']) {
            $user->forceFill(['name' => $data['name']])->save();
        }
    }
}
