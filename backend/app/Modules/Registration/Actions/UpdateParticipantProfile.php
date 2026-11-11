<?php

namespace App\Modules\Registration\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Registration\Models\ParticipantProfile;
use App\Shared\Actions\Action;
use Illuminate\Support\Facades\DB;

/**
 * Creates or updates the profile of an account. The only writer of participant_profiles;
 * used by registration and by ParticipantPortal\UpdateMyProfile.
 */
final class UpdateParticipantProfile extends Action
{
    public static function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'title_before' => ['nullable', 'string', 'max:50'],
            'title_after' => ['nullable', 'string', 'max:50'],
            'organization' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function handle(User $user, array $input): ParticipantProfile
    {
        $data = $this->validate($input);

        return DB::transaction(fn () => ParticipantProfile::query()->updateOrCreate(['user_id' => $user->id], $data));
    }
}
