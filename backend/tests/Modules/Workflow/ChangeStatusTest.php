<?php

use App\Modules\Registration\Models\Registration;
use App\Modules\Workflow\Actions\ChangeStatus;
use App\Modules\Workflow\Enums\PaymentStatus;
use App\Modules\Workflow\Enums\RegistrationStatus;
use App\Modules\Workflow\Enums\StatusCategory;
use App\Modules\Workflow\Enums\SubmissionStatus;
use App\Modules\Workflow\Events\StatusChanged;
use App\Modules\Workflow\Exceptions\InvalidStatusTransition;
use App\Modules\Workflow\Models\StatusHistory;
use App\Shared\Auth\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->conference = makeConference(2027);
    ['registration' => $this->registration] = registerParticipant($this->conference);
    inConference($this->conference);
});

it('allows a prototype transition, writes the history and raises StatusChanged', function () {
    Event::fake([StatusChanged::class]);
    $organizer = staff($this->conference, Role::Organizer);

    app(ChangeStatus::class)->byStaff($organizer, $this->registration, RegistrationStatus::Cancelled, 'No longer coming');

    $history = StatusHistory::query()->latest('id')->first();
    expect(Registration::query()->find($this->registration->id)->status)->toBe(RegistrationStatus::Cancelled)
        ->and($history->from_status)->toBe('received')
        ->and($history->to_status)->toBe('cancelled')
        ->and($history->actor_id)->toBe($organizer->id)
        ->and($history->note)->toBe('No longer coming');
    Event::assertDispatched(StatusChanged::class, fn (StatusChanged $e) => $e->to === 'cancelled');
});

it('refuses a transition that is not in the map', function (StatusCategory $category, BackedEnum $to) {
    app(ChangeStatus::class)->handle($this->registration, $category, $to);
})->with([
    'received → received' => [StatusCategory::Registration, RegistrationStatus::Received],
    'pending → refunded (full version)' => [StatusCategory::Payment, PaymentStatus::Refunded],
    'pending → overdue (full version)' => [StatusCategory::Payment, PaymentStatus::Overdue],
])->throws(InvalidStatusTransition::class);

it('refuses a full-version submission transition', function () {
    $submission = $this->registration->submissions()->first();

    app(ChangeStatus::class)->handle($submission, StatusCategory::Submission, SubmissionStatus::UnderReview);
})->throws(InvalidStatusTransition::class);

it('refuses a second initialization', function () {
    app(ChangeStatus::class)->initialize($this->registration, StatusCategory::Registration, RegistrationStatus::Received);
})->throws(InvalidStatusTransition::class);

it('refuses a status of another category', function () {
    app(ChangeStatus::class)->handle($this->registration, StatusCategory::Registration, PaymentStatus::Paid);
})->throws(InvalidArgumentException::class);

it('cancelled is final in the prototype', function () {
    $organizer = staff($this->conference, Role::Organizer);
    app(ChangeStatus::class)->byStaff($organizer, $this->registration, RegistrationStatus::Cancelled);

    app(ChangeStatus::class)->byStaff($organizer, $this->registration, RegistrationStatus::Confirmed);
})->throws(InvalidStatusTransition::class);

it('programme committee may not change the registration status', function () {
    $committee = staff($this->conference, Role::ProgrammeCommittee);

    app(ChangeStatus::class)->byStaff($committee, $this->registration, RegistrationStatus::Cancelled);
})->throws(AuthorizationException::class);
