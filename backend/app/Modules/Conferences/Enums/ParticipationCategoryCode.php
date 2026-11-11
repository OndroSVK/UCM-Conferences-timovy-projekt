<?php

namespace App\Modules\Conferences\Enums;

enum ParticipationCategoryCode: string
{
    case Speaker = 'speaker';
    case PosterAuthor = 'poster_author';
    case PassiveParticipant = 'passive_participant';
}
