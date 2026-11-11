<?php

namespace App\Shared\Conference;

use App\Modules\Conferences\Models\Conference;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * For every model with a conference_id column (backend.md 4.1).
 *
 * - adds the ConferenceScope global scope,
 * - fills conference_id from the active conference on create,
 * - refuses to write a record of another conference than the active one.
 */
trait BelongsToConference
{
    public static function bootBelongsToConference(): void
    {
        static::addGlobalScope(new ConferenceScope);

        static::creating(function ($model): void {
            $context = app(ConferenceContext::class);

            if ($model->conference_id === null && $context->mode() === ConferenceContext::MODE_CONFERENCE) {
                $model->conference_id = $context->conferenceId();
            }

            static::guardConferenceWrite($model);
        });

        static::updating(fn ($model) => static::guardConferenceWrite($model));
        static::deleting(fn ($model) => static::guardConferenceWrite($model));
    }

    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }

    private static function guardConferenceWrite($model): void
    {
        $context = app(ConferenceContext::class);

        if ($model->conference_id === null) {
            throw new MissingConferenceContext(sprintf('[%s] has no conference_id.', $model::class));
        }

        if ($context->mode() === ConferenceContext::MODE_NONE) {
            throw new MissingConferenceContext(sprintf('Write to [%s] without an active conference context.', $model::class));
        }

        if ($context->mode() === ConferenceContext::MODE_CONFERENCE
            && (int) $model->conference_id !== $context->conferenceId()) {
            throw new MissingConferenceContext(sprintf(
                'Write to [%s] of conference %d inside the context of conference %d.',
                $model::class,
                $model->conference_id,
                $context->conferenceId(),
            ));
        }
    }
}
