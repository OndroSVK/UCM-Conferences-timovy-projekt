<?php

namespace App\Modules\Payments\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Payment status became "paid". Payments knows nothing about invoices or e-mails;
 * Invoicing and Notifications react (backend.md 3.3 rule 2).
 */
final class PaymentSettled implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly int $registrationId,
        public readonly int $conferenceId,
    ) {
    }
}
