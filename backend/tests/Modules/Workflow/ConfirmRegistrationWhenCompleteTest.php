<?php

use App\Modules\Payments\Actions\GrantFeeWaiver;
use App\Modules\Payments\Actions\RecordPayment;
use App\Modules\Submissions\Actions\RecordCommitteeDecision;
use App\Modules\Submissions\Actions\SubmitSubmission;
use App\Modules\Workflow\Actions\ChangeStatus;
use App\Modules\Workflow\Actions\ConfirmRegistrationWhenComplete;
use App\Modules\Workflow\Enums\RegistrationStatus;
use App\Shared\Auth\Role;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->conference = makeConference(2027);
    $this->cashier = paymentsOrganizer($this->conference);
    $this->committee = staff($this->conference, Role::ProgrammeCommittee);
});

function submitAndDecide($test, $user, $registration, string $decision): void
{
    $submission = $registration->submissions()->first();
    app(SubmitSubmission::class)->handle($user, $submission, ['title' => 'Paper', 'file' => fakePdf()]);
    app(RecordCommitteeDecision::class)->handle($test->committee, $submission->fresh(), ['decision' => $decision, 'justification' => 'OK']);
}

it('confirms a speaker after full payment and acceptance', function () {
    ['user' => $user, 'registration' => $registration] = registerParticipant($this->conference, 'speaker');
    inConference($this->conference);

    app(RecordPayment::class)->handle($this->cashier, $registration, ['amount' => '180', 'paid_on' => today()->toDateString()]);
    expect(freshRegistration($registration)->status)->toBe(RegistrationStatus::Received);

    submitAndDecide($this, $user, $registration, 'accepted');
    expect(freshRegistration($registration)->status)->toBe(RegistrationStatus::Confirmed);
});

it('confirms a passive participant after payment only', function () {
    ['registration' => $registration] = registerParticipant($this->conference, 'passive_participant');
    inConference($this->conference);

    app(RecordPayment::class)->handle($this->cashier, $registration, ['amount' => '90', 'paid_on' => today()->toDateString()]);

    expect(freshRegistration($registration)->status)->toBe(RegistrationStatus::Confirmed);
});

it('confirms a passive participant with a fee waiver', function () {
    ['registration' => $registration] = registerParticipant($this->conference, 'passive_participant');
    inConference($this->conference);

    app(GrantFeeWaiver::class)->handle($this->cashier, $registration);

    expect(freshRegistration($registration)->status)->toBe(RegistrationStatus::Confirmed);
});

it('does not confirm after a partial payment', function () {
    ['registration' => $registration] = registerParticipant($this->conference, 'passive_participant');
    inConference($this->conference);

    app(RecordPayment::class)->handle($this->cashier, $registration, ['amount' => '50', 'paid_on' => today()->toDateString()]);

    expect(freshRegistration($registration)->status)->toBe(RegistrationStatus::Received);
});

// PROPOSAL BO3
it('does not confirm automatically on conditional acceptance; an organizer may confirm manually', function () {
    ['user' => $user, 'registration' => $registration] = registerParticipant($this->conference, 'speaker');
    inConference($this->conference);
    app(RecordPayment::class)->handle($this->cashier, $registration, ['amount' => '180', 'paid_on' => today()->toDateString()]);

    submitAndDecide($this, $user, $registration, 'conditionally_accepted');
    expect(freshRegistration($registration)->status)->toBe(RegistrationStatus::Received)
        ->and(app(ConfirmRegistrationWhenComplete::class)->handle($registration->id))->toBeFalse();

    app(ChangeStatus::class)->byStaff(staff($this->conference, Role::Organizer), $registration, RegistrationStatus::Confirmed);
    expect(freshRegistration($registration)->status)->toBe(RegistrationStatus::Confirmed);
});
