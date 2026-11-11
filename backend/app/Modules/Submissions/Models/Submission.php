<?php

namespace App\Modules\Submissions\Models;

use App\Modules\Registration\Models\Registration;
use App\Modules\Submissions\Enums\SubmissionType;
use App\Modules\Workflow\Contracts\HasWorkflowStatus;
use App\Modules\Workflow\Enums\SubmissionStatus;
use App\Shared\Conference\BelongsToConference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * status is not fillable: only ChangeStatus writes it.
 *
 * @property int $id
 * @property int $conference_id
 * @property int $registration_id
 * @property SubmissionType|null $type
 * @property string|null $title
 * @property SubmissionStatus|null $status
 */
class Submission extends Model implements HasWorkflowStatus
{
    use BelongsToConference;

    protected $fillable = ['conference_id', 'registration_id', 'type', 'title'];

    protected function casts(): array
    {
        return [
            'type' => SubmissionType::class,
            'status' => SubmissionStatus::class,
        ];
    }

    public function workflowRegistrationId(): int
    {
        return $this->registration_id;
    }

    public function workflowConferenceId(): int
    {
        return $this->conference_id;
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(SubmissionVersion::class)->orderBy('version');
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(SubmissionVersion::class)->latestOfMany('version');
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(Decision::class)->orderBy('decided_at');
    }
}
