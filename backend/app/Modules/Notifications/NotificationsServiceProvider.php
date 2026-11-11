<?php

namespace App\Modules\Notifications;

use App\Modules\Invoicing\Events\InvoiceIssued;
use App\Modules\Invoicing\Events\InvoiceIssueFailed;
use App\Modules\Notifications\Listeners\SendDecision;
use App\Modules\Notifications\Listeners\SendParticipationConfirmed;
use App\Modules\Notifications\Listeners\SendPaymentReceived;
use App\Modules\Notifications\Listeners\SendRegistrationConfirmation;
use App\Modules\Notifications\Listeners\SendSubmissionReceived;
use App\Modules\Payments\Events\PaymentSettled;
use App\Modules\Registration\Events\RegistrationCreated;
use App\Modules\Submissions\Events\CommitteeDecisionRecorded;
use App\Modules\Submissions\Events\SubmissionSubmitted;
use App\Modules\Workflow\Events\StatusChanged;
use App\Shared\Modules\ModuleServiceProvider;

/** M9 — system e-mails through the queue, email_log. Owner: C. */
final class NotificationsServiceProvider extends ModuleServiceProvider
{
    protected function listeners(): array
    {
        return [
            RegistrationCreated::class => [SendRegistrationConfirmation::class],
            InvoiceIssued::class => [SendPaymentReceived::class],
            InvoiceIssueFailed::class => [SendPaymentReceived::class],
            PaymentSettled::class => [SendPaymentReceived::class],
            SubmissionSubmitted::class => [SendSubmissionReceived::class],
            CommitteeDecisionRecorded::class => [SendDecision::class],
            StatusChanged::class => [SendParticipationConfirmed::class],
        ];
    }

    protected function viewNamespace(): ?string
    {
        return 'notifications';
    }
}
