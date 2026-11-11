<?php

namespace App\Modules\Payments\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

final class PaymentRecorded implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly int $paymentId,
        public readonly int $registrationId,
        public readonly int $conferenceId,
    ) {
    }
}
