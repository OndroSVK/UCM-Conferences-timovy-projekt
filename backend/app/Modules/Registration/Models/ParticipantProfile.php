<?php

namespace App\Modules\Registration\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $title_before
 * @property string|null $title_after
 * @property string $organization
 */
class ParticipantProfile extends Model
{
    protected $fillable = ['user_id', 'first_name', 'last_name', 'title_before', 'title_after', 'organization'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** "doc. Ing. Jana Nováková, PhD." */
    public function fullName(): string
    {
        $name = trim(implode(' ', array_filter([$this->title_before, $this->first_name, $this->last_name])));

        return $this->title_after ? "{$name}, {$this->title_after}" : $name;
    }
}
