<?php

use App\Modules\Auth\Models\User;
use App\Modules\Notifications\Enums\EmailType;
use App\Modules\Notifications\Models\EmailLog;
use App\Modules\Registration\Actions\RegisterNewParticipant;
use App\Modules\Registration\Models\ParticipantProfile;
use App\Modules\Registration\Models\Registration;
use App\Modules\Submissions\Models\Submission;
use App\Modules\Workflow\Enums\PaymentStatus;
use App\Modules\Workflow\Enums\RegistrationStatus;
use App\Modules\Workflow\Enums\SubmissionStatus;
use App\Modules\Workflow\Models\StatusHistory;
use App\Shared\Auth\AccessControl;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

// US-03
it('creates account, profile and registration with initial statuses, price snapshot and VS', function () {
    $conference = makeConference(2027);

    ['user' => $user, 'registration' => $registration] = registerParticipant($conference, 'speaker');

    inConference($conference);
    $registration = Registration::query()->findOrFail($registration->id);
    $submission = Submission::query()->where('registration_id', $registration->id)->firstOrFail();

    expect($user->exists)->toBeTrue()
        ->and(app(AccessControl::class)->isParticipant($user))->toBeTrue()
        ->and(ParticipantProfile::query()->where('user_id', $user->id)->exists())->toBeTrue()
        ->and($registration->status)->toBe(RegistrationStatus::Received)
        ->and($registration->payment_status)->toBe(PaymentStatus::Pending)
        ->and($submission->status)->toBe(SubmissionStatus::Draft)
        ->and($registration->price_cents)->toBe(18000)
        ->and($registration->currency)->toBe('EUR')
        ->and($registration->variable_symbol)->toBe('27000001')
        ->and($registration->data_processing_consent_at)->not->toBeNull();

    // initial statuses are recorded in the history
    expect(StatusHistory::query()->where('registration_id', $registration->id)->count())->toBe(3);
});

it('sends the confirmation with payment details', function () {
    $conference = makeConference(2027);
    registerParticipant($conference, 'speaker', ['email' => 'speaker@example.test']);

    inConference($conference);
    $log = EmailLog::query()->where('type', EmailType::RegistrationConfirmation->value)->first();

    expect($log?->recipient)->toBe('speaker@example.test')
        ->and($log?->result)->toBe(EmailLog::SENT);
});

it('keeps the price snapshot when the price list changes later', function () {
    $conference = makeConference(2027);
    ['registration' => $registration] = registerParticipant($conference, 'poster_author');

    inConference($conference);
    \App\Modules\Conferences\Models\ParticipationCategory::query()->where('code', 'poster_author')->update(['price_cents' => 50000]);

    expect(Registration::query()->find($registration->id)->price_cents)->toBe(12000);
});

it('sets not_required for a passive participant', function () {
    $conference = makeConference(2027);
    ['registration' => $registration] = registerParticipant($conference, 'passive_participant');

    inConference($conference);
    expect(Submission::query()->where('registration_id', $registration->id)->first()->status)->toBe(SubmissionStatus::NotRequired);
});

// US-04
it('creates no second account for an existing e-mail', function () {
    $conference = makeConference(2027);
    registerParticipant($conference, 'speaker', ['email' => 'same@example.test']);

    expect(fn () => registerParticipant($conference, 'speaker', ['email' => 'same@example.test']))
        ->toThrow(ValidationException::class);

    expect(User::query()->where('email', 'same@example.test')->count())->toBe(1);
});

it('validates the category against the conference', function () {
    $conference = makeConference(2027);

    expect(fn () => registerParticipant($conference, 'vip'))->toThrow(ValidationException::class);
});

it('requires consent with data processing', function () {
    $conference = makeConference(2027);

    expect(fn () => registerParticipant($conference, 'speaker', ['data_processing_consent' => null]))
        ->toThrow(ValidationException::class);
});

it('refuses registration after the deadline', function () {
    $conference = makeConference(2027, ['registration_deadline' => now()->subDay()->toDateString()]);

    expect(fn () => registerParticipant($conference))->toThrow(\App\Shared\Actions\DomainException::class);
    expect(User::query()->count())->toBe(0); // the whole transaction is rolled back
});

it('a zero price creates no payment request', function () {
    $conference = makeConference(2027);
    inConference($conference);
    \App\Modules\Conferences\Models\ParticipationCategory::query()->where('code', 'passive_participant')->update(['price_cents' => 0]);
    conferenceContext()->clear();

    ['registration' => $registration] = registerParticipant($conference, 'passive_participant');

    expect(freshRegistration($registration)->payment_status)->toBeNull();
});

it('a mail failure does not undo the registration', function () {
    $conference = makeConference(2027);
    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP down'));

    ['registration' => $registration] = app(RegisterNewParticipant::class)->handle($conference, registrationInput());

    expect(freshRegistration($registration)->exists)->toBeTrue();
});
