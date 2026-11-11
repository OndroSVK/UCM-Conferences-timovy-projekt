<?php

namespace App\Modules\Registration\Actions;

use App\Modules\Auth\Actions\CreateUserAccount;
use App\Modules\Auth\Models\User;
use App\Modules\Conferences\Models\Conference;
use App\Modules\Registration\Models\ParticipantProfile;
use App\Modules\Registration\Models\Registration;
use App\Shared\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * US-04: a logged-in user registers for another conference without a new account.
 * A second registration for the same conference is refused (also by a unique index).
 * Profile fields are required only when the account has no profile yet (e.g. staff).
 */
final class RegisterExistingUserForConference extends Action
{
    public function __construct(
        private readonly CreateUserAccount $createAccount,
        private readonly UpdateParticipantProfile $updateProfile,
        private readonly CreateRegistration $createRegistration,
    ) {
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function handle(User $actor, Conference $conference, array $input): Registration
    {
        $hasProfile = ParticipantProfile::query()->where('user_id', $actor->id)->exists();

        $rules = $this->createRegistration->rulesFor($conference);
        if (! $hasProfile) {
            $rules = array_merge(UpdateParticipantProfile::rules(), $rules);
        }

        $data = Validator::make($input, $rules)->validate();

        return DB::transaction(function () use ($actor, $conference, $data, $hasProfile) {
            if (! $hasProfile) {
                $this->updateProfile->handle($actor, $data);
            }

            $this->createAccount->ensureParticipantRole($actor);

            return $this->createRegistration->handle($actor, $conference, $data, newAccount: false);
        });
    }
}
