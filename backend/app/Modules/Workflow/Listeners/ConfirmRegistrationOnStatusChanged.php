<?php

namespace App\Modules\Workflow\Listeners;

use App\Modules\Workflow\Actions\ConfirmRegistrationWhenComplete;
use App\Modules\Workflow\Enums\StatusCategory;
use App\Modules\Workflow\Events\StatusChanged;
use App\Shared\Conference\ConferenceContext;

/**
 * After a payment or submission status change, checks whether the registration
 * can be confirmed. Runs synchronously after commit (no side effects outside DB).
 */
final class ConfirmRegistrationOnStatusChanged
{
    public function __construct(
        private readonly ConfirmRegistrationWhenComplete $confirm,
        private readonly ConferenceContext $context,
    ) {
    }

    public function handle(StatusChanged $event): void
    {
        if ($event->category === StatusCategory::Registration) {
            return;
        }

        $this->context->runForConference(
            $event->conferenceId,
            fn () => $this->confirm->handle($event->registrationId),
        );
    }
}
