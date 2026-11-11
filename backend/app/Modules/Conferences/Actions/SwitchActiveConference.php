<?php

namespace App\Modules\Conferences\Actions;

use App\Modules\Conferences\Models\Conference;
use App\Shared\Actions\Action;
use App\Shared\Auth\AccessControl;
use App\Shared\Conference\ConferenceContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Admin conference switcher. Only an assigned conference (BR4); superadmin any.
 * The caller (HTTP layer) stores the returned id in the session.
 */
final class SwitchActiveConference extends Action
{
    public function __construct(
        private readonly AccessControl $access,
        private readonly ConferenceContext $context,
    ) {
    }

    public function handle(Authenticatable $actor, int $conferenceId): Conference
    {
        $conference = Conference::query()->find($conferenceId);

        if ($conference === null || ! $this->access->canAccessConference($actor, $conference->id)) {
            throw new AuthorizationException(__('errors.conference_not_assigned'));
        }

        $this->context->setConference($conference->id);

        return $conference;
    }
}
