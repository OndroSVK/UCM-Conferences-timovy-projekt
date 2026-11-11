<?php

use App\Modules\Submissions\Actions\RecordCommitteeDecision;
use App\Modules\Submissions\Actions\SubmitSubmission;
use App\Modules\Submissions\Enums\DecisionType;
use App\Modules\Submissions\Models\Decision;
use App\Modules\Workflow\Enums\SubmissionStatus;
use App\Shared\Actions\DomainException;
use App\Shared\Auth\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    Storage::fake('local');
    $this->conference = makeConference(2027);
    ['user' => $this->author, 'registration' => $registration] = registerParticipant($this->conference, 'speaker');
    inConference($this->conference);
    $this->submission = $registration->submissions()->first();
    $this->version = app(SubmitSubmission::class)->handle($this->author, $this->submission, ['title' => 'Paper', 'file' => fakePdf()]);
    $this->committee = staff($this->conference, Role::ProgrammeCommittee);
});

it('records the decision for the latest version and changes the status', function (string $decision, SubmissionStatus $status) {
    $record = app(RecordCommitteeDecision::class)->handle($this->committee, $this->submission->fresh(), [
        'decision' => $decision,
        'justification' => 'Clear methodology.',
    ]);

    expect($record->decision)->toBe(DecisionType::from($decision))
        ->and($record->submission_version_id)->toBe($this->version->id)
        ->and($record->decided_by)->toBe($this->committee->id)
        ->and($this->submission->fresh()->status)->toBe($status);
})->with([
    ['accepted', SubmissionStatus::Accepted],
    ['conditionally_accepted', SubmissionStatus::ConditionallyAccepted],
    ['rejected', SubmissionStatus::Rejected],
]);

// R30
it('an organizer may not decide', function () {
    app(RecordCommitteeDecision::class)->handle(staff($this->conference, Role::Organizer), $this->submission->fresh(), [
        'decision' => 'accepted', 'justification' => 'x',
    ]);
})->throws(AuthorizationException::class);

it('the committee of another conference may not decide', function () {
    app(RecordCommitteeDecision::class)->handle(staff(makeConference(2028), Role::ProgrammeCommittee), $this->submission->fresh(), [
        'decision' => 'accepted', 'justification' => 'x',
    ]);
})->throws(AuthorizationException::class);

it('single round: a second decision is refused', function () {
    $decide = fn () => app(RecordCommitteeDecision::class)->handle($this->committee, $this->submission->fresh(), [
        'decision' => 'accepted', 'justification' => 'x',
    ]);

    $decide();
    expect($decide)->toThrow(DomainException::class);
    expect(Decision::query()->count())->toBe(1);
});

it('requires a justification and a known decision', function (array $input) {
    app(RecordCommitteeDecision::class)->handle($this->committee, $this->submission->fresh(), $input);
})->with([
    [['decision' => 'accepted', 'justification' => '']],
    [['decision' => 'revision_requested', 'justification' => 'x']], // full version
])->throws(ValidationException::class);
