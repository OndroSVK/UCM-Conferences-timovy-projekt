<?php

use App\Modules\ParticipantPortal\Actions\GetPaymentInstructions;
use App\Modules\Payments\Actions\RecordPayment;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->conference = makeConference(2027);
    ['user' => $this->owner, 'registration' => $this->registration] = registerParticipant($this->conference, 'poster_author');
    inConference($this->conference);
});

// R1
it('gives IBAN, payee, amount, own VS and due date from the conference', function () {
    $instructions = app(GetPaymentInstructions::class)->handle($this->owner, $this->registration);

    expect($instructions['iban'])->toBe($this->conference->iban)
        ->and($instructions['payee'])->toBe($this->conference->payee_name)
        ->and($instructions['amount']->format())->toBe('120.00 EUR')
        ->and($instructions['variable_symbol'])->toBe($this->registration->variable_symbol)
        ->and($instructions['due_date']->toDateString())->toBe($this->conference->payment_deadline->toDateString());
});

it('gives no payment request when fully paid', function () {
    app(RecordPayment::class)->handle(paymentsOrganizer($this->conference), $this->registration, ['amount' => '120', 'paid_on' => today()->toDateString()]);

    expect(app(GetPaymentInstructions::class)->handle($this->owner, freshRegistration($this->registration)))->toBeNull();
});

it('is only for the owner', function () {
    ['user' => $other] = registerParticipant($this->conference);

    app(GetPaymentInstructions::class)->handle($other, $this->registration);
})->throws(AuthorizationException::class);
