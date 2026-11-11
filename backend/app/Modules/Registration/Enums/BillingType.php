<?php

namespace App\Modules\Registration\Enums;

/** Who pays: the participant, the university or another workplace. */
enum BillingType: string
{
    case Participant = 'participant';
    case University = 'university';
    case Workplace = 'workplace';
}
