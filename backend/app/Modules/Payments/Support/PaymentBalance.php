<?php

namespace App\Modules\Payments\Support;

use App\Shared\Money\Money;

final readonly class PaymentBalance
{
    public function __construct(
        /** Price snapshot of the registration. */
        public Money $price,
        /** Amount the participant has to pay: 0 with a fee waiver. */
        public Money $due,
        public Money $paid,
        /** Remaining amount, never negative. */
        public Money $remaining,
        /** Paid above the due amount (BO6). */
        public Money $overpaid,
        public bool $feeWaived,
    ) {
    }

    public function requiresPayment(): bool
    {
        return $this->remaining->isPositive();
    }
}
