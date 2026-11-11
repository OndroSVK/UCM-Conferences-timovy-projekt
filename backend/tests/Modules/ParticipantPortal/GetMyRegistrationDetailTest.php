<?php

use App\Modules\ParticipantPortal\Actions\GetMyRegistrationDetail;
use App\Modules\Payments\Actions\RecordPayment;
use App\Modules\Workflow\Enums\PaymentStatus;
use App\Modules\Workflow\Enums\RegistrationStatus;
use App\Modules\Workflow\Enums\SubmissionStatus;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;

// US-08
it('shows the three statuses, price, paid, remaining and instructions with own VS', function () {
    Storage::fake('local');
    $conference = makeConference(2027);
    ['user' => $user, 'registration' => $registration] = registerParticipant($conference, 'speaker');
    inConference($conference);
    app(RecordPayment::class)->handle(paymentsOrganizer($conference), $registration, ['amount' => '100', 'paid_on' => today()->toDateString()]);
    conferenceContext()->clear();

    $detail = app(GetMyRegistrationDetail::class)->handle($user, $registration->id);

    expect($detail['statuses'])->toBe([
        'registration' => RegistrationStatus::Received,
        'payment' => PaymentStatus::PartiallyPaid,
        'submission' => SubmissionStatus::Draft,
    ])
        ->and($detail['balance']->paid->format())->toBe('100.00 EUR')
        ->and($detail['balance']->remaining->format())->toBe('80.00 EUR')
        ->and($detail['payment_instructions']['variable_symbol'])->toBe($registration->variable_symbol)
        ->and($detail['payment_instructions']['amount']->format())->toBe('80.00 EUR');
});

it('a registration of someone else is not found', function () {
    $conference = makeConference(2027);
    ['registration' => $foreign] = registerParticipant($conference);
    ['user' => $user] = registerParticipant($conference);

    app(GetMyRegistrationDetail::class)->handle($user, $foreign->id);
})->throws(ModelNotFoundException::class);
