<?php

use App\Modules\Payments\Actions\GetPaymentBalance;
use App\Modules\Payments\Actions\RecordPayment;
use Illuminate\Support\Facades\Storage;

it('computes price, paid, remaining and overpayment', function () {
    Storage::fake('local');
    $conference = makeConference(2027);
    $cashier = paymentsOrganizer($conference);
    ['registration' => $registration] = registerParticipant($conference, 'poster_author');
    inConference($conference);
    $balance = app(GetPaymentBalance::class);

    $initial = $balance->handle($registration);
    expect($initial->price->format())->toBe('120.00 EUR')
        ->and($initial->remaining->format())->toBe('120.00 EUR')
        ->and($initial->requiresPayment())->toBeTrue();

    app(RecordPayment::class)->handle($cashier, $registration, ['amount' => '150', 'paid_on' => today()->toDateString()]);

    $after = $balance->handle(freshRegistration($registration));
    expect($after->paid->format())->toBe('150.00 EUR')
        ->and($after->remaining->isZero())->toBeTrue()
        ->and($after->overpaid->format())->toBe('30.00 EUR');
});
