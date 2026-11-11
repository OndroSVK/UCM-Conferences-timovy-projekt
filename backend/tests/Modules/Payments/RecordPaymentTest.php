<?php

use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Payments\Actions\RecordPayment;
use App\Modules\Payments\Events\PaymentSettled;
use App\Modules\Payments\Models\Payment;
use App\Modules\Workflow\Enums\PaymentStatus;
use App\Shared\Actions\DomainException;
use App\Shared\Auth\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    Storage::fake('local');
    $this->conference = makeConference(2027);
    $this->cashier = paymentsOrganizer($this->conference);
    ['registration' => $this->registration] = registerParticipant($this->conference, 'speaker');
    inConference($this->conference);
    $this->pay = fn (string $amount, $actor = null) => app(RecordPayment::class)->handle(
        $actor ?? $this->cashier,
        $this->registration,
        ['amount' => $amount, 'paid_on' => today()->toDateString()],
    );
});

// US-12
it('a partial payment gives partially_paid with the remaining amount', function () {
    ['balance' => $balance] = ($this->pay)('100');

    expect(freshRegistration($this->registration)->payment_status)->toBe(PaymentStatus::PartiallyPaid)
        ->and($balance->remaining->format())->toBe('80.00 EUR');
});

it('the full amount in parts gives paid and PaymentSettled once', function () {
    Event::fake([PaymentSettled::class]);

    ($this->pay)('100');
    ($this->pay)('50');
    ['balance' => $balance] = ($this->pay)('30');

    expect(freshRegistration($this->registration)->payment_status)->toBe(PaymentStatus::Paid)
        ->and($balance->remaining->isZero())->toBeTrue()
        ->and(Payment::query()->count())->toBe(3);
    Event::assertDispatchedTimes(PaymentSettled::class, 1);
});

it('a second payment after paid creates no second invoice', function () {
    ($this->pay)('180');
    ['balance' => $balance] = ($this->pay)('20');

    expect(Invoice::query()->count())->toBe(1)
        ->and($balance->overpaid->format())->toBe('20.00 EUR'); // BO6 warning for the admin
});

it('stores amount, date, VS and who recorded it', function () {
    ['payment' => $payment] = ($this->pay)('120,50');

    expect($payment->amount_cents)->toBe(12050)
        ->and($payment->variable_symbol)->toBe($this->registration->variable_symbol)
        ->and($payment->recorded_by)->toBe($this->cashier->id);
});

it('refuses zero, negative and malformed amounts', function (string $amount) {
    ($this->pay)($amount);
})->with(['0', '-10', 'abc', '10.123'])->throws(ValidationException::class);

it('refuses a payment date in the future', function () {
    app(RecordPayment::class)->handle($this->cashier, $this->registration, ['amount' => '10', 'paid_on' => today()->addDay()->toDateString()]);
})->throws(ValidationException::class);

it('an organizer without delegation may not record payments', function () {
    ($this->pay)('10', staff($this->conference, Role::Organizer));
})->throws(AuthorizationException::class);

it('a delegated organizer of another conference may not record payments here', function () {
    ($this->pay)('10', paymentsOrganizer(makeConference(2028)));
})->throws(AuthorizationException::class);

it('refuses a payment when no payment is required', function () {
    ['registration' => $free] = registerParticipant($this->conference, 'passive_participant');
    app(\App\Modules\Payments\Actions\GrantFeeWaiver::class)->handle($this->cashier, $free);

    app(RecordPayment::class)->handle($this->cashier, $free, ['amount' => '10', 'paid_on' => today()->toDateString()]);
})->throws(DomainException::class);
