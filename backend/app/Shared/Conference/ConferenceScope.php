<?php

namespace App\Shared\Conference;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Limits every query on a conference-bound model to the active context.
 * A query without context throws instead of returning data of all conferences.
 */
final class ConferenceScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(ConferenceContext::class);
        $column = $model->qualifyColumn('conference_id');

        match ($context->mode()) {
            ConferenceContext::MODE_CONFERENCE => $builder->where($column, $context->conferenceId()),
            ConferenceContext::MODE_OWNER => $builder->whereIn(
                $column,
                fn ($query) => $query->select('conference_id')
                    ->from('registrations')
                    ->where('user_id', $context->ownerUserId()),
            ),
            ConferenceContext::MODE_ALL => null,
            default => throw new MissingConferenceContext(sprintf(
                'Query on [%s] without an active conference context.',
                $model::class,
            )),
        };
    }
}
