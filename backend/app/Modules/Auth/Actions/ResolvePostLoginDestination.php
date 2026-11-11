<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Enums\LoginDestination;
use App\Shared\Actions\Action;
use App\Shared\Auth\AccessControl;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Destination after login by roles (R11): participant → user page, staff → admin,
 * both → choice (R17).
 */
final class ResolvePostLoginDestination extends Action
{
    public function __construct(private readonly AccessControl $access)
    {
    }

    public function handle(Authenticatable $user): LoginDestination
    {
        $staff = $this->access->isStaff($user);
        $participant = $this->access->isParticipant($user);

        return match (true) {
            $staff && $participant => LoginDestination::Choose,
            $staff => LoginDestination::Admin,
            default => LoginDestination::ParticipantPortal,
        };
    }
}
