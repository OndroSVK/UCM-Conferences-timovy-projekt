<?php

namespace App\Modules\Conferences\Models;

use App\Modules\Auth\Models\User;
use App\Shared\Auth\Role;
use App\Shared\Conference\BelongsToConference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Staff role of a user in one conference (table conference_user, BR4, BR8).
 *
 * @property int $conference_id
 * @property int $user_id
 * @property Role $role
 * @property list<string>|null $extra_permissions
 */
class ConferenceAssignment extends Model
{
    use BelongsToConference;

    protected $table = 'conference_user';

    protected $fillable = ['conference_id', 'user_id', 'role', 'extra_permissions', 'assigned_by'];

    protected function casts(): array
    {
        return [
            'role' => Role::class,
            'extra_permissions' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
