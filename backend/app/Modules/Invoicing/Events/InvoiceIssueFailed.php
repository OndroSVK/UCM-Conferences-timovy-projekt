<?php

namespace App\Modules\Invoicing\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

final class InvoiceIssueFailed implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly int $invoiceId,
        public readonly int $registrationId,
        public readonly int $conferenceId,
    ) {
    }
}
