<?php

namespace App\Modules\Payments\Actions;

use App\Modules\Payments\Models\Payment;
use App\Modules\Payments\Support\PaymentBalance;
use App\Modules\Registration\Models\Registration;
use App\Shared\Actions\Action;
use App\Shared\Money\Money;

/**
 * Price, sum of payments, remaining amount and overpayment of a registration.
 * Pure calculation; the calling feature checks who may see it.
 */
final class GetPaymentBalance extends Action
{
    public function handle(Registration $registration): PaymentBalance
    {
        $currency = $registration->currency;
        $price = Money::of($registration->price_cents, $currency);
        $due = $registration->fee_waived ? Money::zero($currency) : $price;
        $paid = Money::of(
            (int) Payment::query()->where('registration_id', $registration->id)->sum('amount_cents'),
            $currency,
        );
        $difference = $due->subtract($paid);

        return new PaymentBalance(
            price: $price,
            due: $due,
            paid: $paid,
            remaining: $difference->max(Money::zero($currency)),
            overpaid: Money::of(max(0, -$difference->cents), $currency),
            feeWaived: $registration->fee_waived,
        );
    }
}
