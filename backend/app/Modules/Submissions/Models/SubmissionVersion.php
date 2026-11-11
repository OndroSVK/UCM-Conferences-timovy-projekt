<?php

namespace App\Modules\Submissions\Models;

use App\Shared\Conference\BelongsToConference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $submission_id
 * @property int $version
 * @property string $path
 * @property string $original_name
 * @property int $size_bytes
 * @property \Illuminate\Support\Carbon $submitted_at
 */
class SubmissionVersion extends Model
{
    use BelongsToConference;

    protected $fillable = [
        'conference_id', 'submission_id', 'version', 'path', 'original_name',
        'size_bytes', 'sha256', 'uploaded_by', 'submitted_at',
    ];

    protected $hidden = ['path'];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime', 'version' => 'integer', 'size_bytes' => 'integer'];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }
}
