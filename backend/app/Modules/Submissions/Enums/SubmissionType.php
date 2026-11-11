<?php

namespace App\Modules\Submissions\Enums;

use App\Modules\Conferences\Enums\SubmissionRequirement;

enum SubmissionType: string
{
    case Talk = 'talk';
    case Poster = 'poster';

    public static function fromRequirement(SubmissionRequirement $requirement): ?self
    {
        return match ($requirement) {
            SubmissionRequirement::Abstract => self::Talk,
            SubmissionRequirement::Poster => self::Poster,
            SubmissionRequirement::None => null,
        };
    }
}
