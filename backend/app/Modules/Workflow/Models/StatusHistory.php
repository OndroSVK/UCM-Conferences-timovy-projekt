<?php

namespace App\Modules\Workflow\Models;

use App\Modules\Auth\Models\User;
use App\Modules\Workflow\Enums\StatusCategory;
use App\Shared\Conference\BelongsToConference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $registration_id
 * @property StatusCategory $category
 * @property string|null $from_status
 * @property string|null $to_status
 * @property int|null $actor_id
 * @property string|null $note
 */
class StatusHistory extends Model
{
    use BelongsToConference;

    public const UPDATED_AT = null;

    protected $table = 'status_history';

    protected $fillable = [
        'conference_id', 'registration_id', 'category', 'subject_type', 'subject_id',
        'from_status', 'to_status', 'actor_id', 'note',
    ];

    protected function casts(): array
    {
        return ['category' => StatusCategory::class, 'created_at' => 'datetime'];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
