<?php

namespace App\Modules\Conferences\Models;

use App\Shared\Conference\BelongsToConference;
use Illuminate\Database\Eloquent\Model;

/**
 * Option of a registration form list, e.g. field "industry".
 *
 * @property string $field
 * @property string $value
 * @property string $label
 */
class FormField extends Model
{
    use BelongsToConference;

    public const INDUSTRY = 'industry';

    protected $fillable = ['conference_id', 'field', 'value', 'label', 'sort'];
}
