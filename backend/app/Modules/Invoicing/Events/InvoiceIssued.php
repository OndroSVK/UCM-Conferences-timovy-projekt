<?php

namespace App\Modules\Invoicing\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

final class InvoiceIssued implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly int $invoiceId,
        public readonly int $registrationId,
        public readonly int $conferenceId,
    ) {
    }
}
