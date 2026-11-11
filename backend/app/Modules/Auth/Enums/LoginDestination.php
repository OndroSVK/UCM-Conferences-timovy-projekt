<?php

namespace App\Modules\Auth\Enums;

/** Where a user goes after login (R11, R17). */
enum LoginDestination: string
{
    case ParticipantPortal = 'participant_portal';
    case Admin = 'admin';
    /** The user has both roles and chooses. */
    case Choose = 'choose';
}
