<?php

namespace App\Modules\Conferences\Models;

use App\Modules\Conferences\Enums\ParticipationCategoryCode;
use App\Modules\Conferences\Enums\SubmissionRequirement;
use App\Shared\Conference\BelongsToConference;
use App\Shared\Money\Money;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $conference_id
 * @property ParticipationCategoryCode $code
 * @property string $name
 * @property int $price_cents
 * @property SubmissionRequirement $submission_requirement
 */
class ParticipationCategory extends Model
{
    use BelongsToConference;

    protected $fillable = ['conference_id', 'code', 'name', 'price_cents', 'submission_requirement', 'sort'];

    protected function casts(): array
    {
        return [
            'code' => ParticipationCategoryCode::class,
            'submission_requirement' => SubmissionRequirement::class,
            'price_cents' => 'integer',
        ];
    }

    public function price(string $currency): Money
    {
        return Money::of($this->price_cents, $currency);
    }
}
