<?php

namespace App\Modules\ParticipantPortal\Actions;

use App\Modules\Registration\Models\Registration;
use App\Shared\Actions\Action;
use App\Shared\Conference\ConferenceContext;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

/**
 * US-08: conferences of the participant, newest conference first. Always own
 * registrations only, regardless of the subdomain (PROPOSAL: BO7).
 */
final class ListMyConferences extends Action
{
    public function __construct(private readonly ConferenceContext $context)
    {
    }

    /**
     * @return Collection<int, Registration>
     */
    public function handle(Authenticatable $actor): Collection
    {
        $userId = (int) $actor->getAuthIdentifier();

        return $this->context->runAsOwner($userId, fn () => Registration::query()
            ->where('registrations.user_id', $userId)
            ->join('conferences', 'conferences.id', '=', 'registrations.conference_id')
            ->orderByDesc('conferences.starts_on')
            ->select('registrations.*')
            ->with(['conference', 'category', 'submissions'])
            ->get());
    }
}
