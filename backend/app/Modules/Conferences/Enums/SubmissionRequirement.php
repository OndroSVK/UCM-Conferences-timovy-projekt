<?php

namespace App\Modules\Conferences\Enums;

enum SubmissionRequirement: string
{
    case Abstract = 'abstract';
    case Poster = 'poster';
    case None = 'none';
}
