<?php

namespace App\Modules\ParticipantPortal\Actions;

use App\Modules\Auth\Actions\ChangeEmail;
use App\Modules\Auth\Actions\UpdateAccountName;
use App\Modules\Auth\Models\User;
use App\Modules\Registration\Actions\UpdateParticipantProfile;
use App\Modules\Registration\Models\ParticipantProfile;
use App\Shared\Actions\Action;
use Illuminate\Support\Facades\DB;

/**
 * Own profile. A new e-mail must be verified again (Auth\ChangeEmail). Billing data of
 * existing registrations are not rewritten (U02).
 */
final class UpdateMyProfile extends Action
{
    public function __construct(
        private readonly UpdateParticipantProfile $updateProfile,
        private readonly UpdateAccountName $updateName,
        private readonly ChangeEmail $changeEmail,
    ) {
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function handle(User $actor, array $input): ParticipantProfile
    {
        $profile = DB::transaction(function () use ($actor, $input) {
            $profile = $this->updateProfile->handle($actor, $input);
            $this->updateName->handle($actor, $profile->first_name.' '.$profile->last_name);

            return $profile;
        });

        if (isset($input['email']) && is_string($input['email'])) {
            $this->changeEmail->handle($actor, $input['email']);
        }

        return $profile;
    }
}
