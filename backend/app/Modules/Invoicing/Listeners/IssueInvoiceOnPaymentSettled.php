<?php

namespace App\Modules\Invoicing\Listeners;

use App\Modules\Invoicing\Actions\IssueInvoice;
use App\Modules\Payments\Events\PaymentSettled;
use App\Shared\Conference\ConferenceContext;
use App\Shared\Modules\Modules;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Queued after commit: PDF rendering never slows down or undoes recording a payment.
 */
final class IssueInvoiceOnPaymentSettled implements ShouldQueue
{
    public bool $afterCommit = true;

    public function handle(PaymentSettled $event): void
    {
        if (! Modules::enabled('Invoicing')) {
            return;
        }

        app(ConferenceContext::class)->runForConference(
            $event->conferenceId,
            fn () => app(IssueInvoice::class)->handle($event->registrationId),
        );
    }
}
