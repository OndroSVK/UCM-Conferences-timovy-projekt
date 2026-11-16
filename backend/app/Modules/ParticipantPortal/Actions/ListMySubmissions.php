<?php

namespace App\Modules\ParticipantPortal\Actions;

use App\Modules\Submissions\Models\Submission;
use App\Shared\Actions\Action;
use App\Shared\Conference\ConferenceContext;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

/** Own submissions across conferences; conference scope alone does not enforce ownership. */
final class ListMySubmissions extends Action
{
    public function __construct(private readonly ConferenceContext $context) {}

    public function handle(Authenticatable $actor): Collection
    {
        $userId = (int) $actor->getAuthIdentifier();

        return $this->context->runAsOwner($userId, fn () => Submission::query()
            ->whereHas('registration', fn ($query) => $query->where('user_id', $userId))
            ->where('status', '!=', 'not_required')
            ->with(['conference', 'registration', 'latestVersion'])
            ->orderByDesc('updated_at')
            ->get());
    }
}
