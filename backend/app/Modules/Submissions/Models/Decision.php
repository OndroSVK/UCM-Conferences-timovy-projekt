<?php

namespace App\Modules\Submissions\Models;

use App\Modules\Submissions\Enums\DecisionType;
use App\Shared\Conference\BelongsToConference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $submission_id
 * @property int $submission_version_id
 * @property DecisionType $decision
 * @property string $justification
 * @property \Illuminate\Support\Carbon $decided_at
 * @property \Illuminate\Support\Carbon|null $notified_at
 */
class Decision extends Model
{
    use BelongsToConference;

    protected $fillable = [
        'conference_id', 'submission_id', 'submission_version_id', 'decision',
        'justification', 'decided_by', 'decided_at', 'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'decision' => DecisionType::class,
            'decided_at' => 'datetime',
            'notified_at' => 'datetime',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(SubmissionVersion::class, 'submission_version_id');
    }
}
