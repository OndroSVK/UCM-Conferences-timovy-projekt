<?php

namespace App\Modules\Submissions\Actions;

use App\Modules\Submissions\Models\Decision;
use App\Shared\Actions\Action;

/**
 * Records that the author was informed about the decision (DB12). Called by
 * Notifications\SendDecision after a successful send, so only Submissions writes decisions.
 */
final class MarkDecisionNotified extends Action
{
    public function handle(int $decisionId): void
    {
        Decision::query()->whereKey($decisionId)->whereNull('notified_at')->first()
            ?->forceFill(['notified_at' => now()])->save();
    }
}
