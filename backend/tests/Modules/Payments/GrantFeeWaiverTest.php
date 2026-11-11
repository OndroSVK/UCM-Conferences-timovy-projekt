<?php

use App\Modules\ParticipantPortal\Actions\GetPaymentInstructions;
use App\Modules\Payments\Actions\GrantFeeWaiver;
use App\Modules\Payments\Actions\RecordPayment;
use App\Shared\Actions\DomainException;
use App\Shared\Auth\Role;
use Illuminate\Auth\Access\AuthorizationException;

beforeEach(function () {
    $this->conference = makeConference(2027);
    $this->cashier = paymentsOrganizer($this->conference);
    ['user' => $this->owner, 'registration' => $this->registration] = registerParticipant($this->conference, 'speaker');
    inConference($this->conference);
});

// R32, BO4
it('waives the fee: property set, no payment status, no payment request', function () {
    app(GrantFeeWaiver::class)->handle($this->cashier, $this->registration, ['note' => 'Invited guest']);

    $fresh = freshRegistration($this->registration);
    expect($fresh->fee_waived)->toBeTrue()
        ->and($fresh->payment_status)->toBeNull()
        ->and($fresh->price_cents)->toBe(18000) // snapshot stays
        ->and(app(GetPaymentInstructions::class)->handle($this->owner, $fresh))->toBeNull();
});

it('is refused after a payment was recorded', function () {
    app(RecordPayment::class)->handle($this->cashier, $this->registration, ['amount' => '10', 'paid_on' => today()->toDateString()]);

    app(GrantFeeWaiver::class)->handle($this->cashier, $this->registration);
})->throws(DomainException::class);

it('is refused twice', function () {
    app(GrantFeeWaiver::class)->handle($this->cashier, $this->registration);
    app(GrantFeeWaiver::class)->handle($this->cashier, freshRegistration($this->registration));
})->throws(DomainException::class);

it('requires the delegated permission', function () {
    app(GrantFeeWaiver::class)->handle(staff($this->conference, Role::Organizer), $this->registration);
})->throws(AuthorizationException::class);
