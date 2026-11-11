<?php

namespace App\Modules\Workflow\Events;

use App\Modules\Workflow\Enums\StatusCategory;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Raised by ChangeStatus after the transaction commits. Carries only ids and values,
 * so queued listeners never see uncommitted data.
 */
final class StatusChanged implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly StatusCategory $category,
        public readonly string $subjectType,
        public readonly int $subjectId,
        public readonly int $registrationId,
        public readonly int $conferenceId,
        public readonly ?string $from,
        public readonly ?string $to,
        public readonly ?int $actorId,
    ) {
    }

    public function is(StatusCategory $category, ?string $to): bool
    {
        return $this->category === $category && $this->to === $to;
    }
}
