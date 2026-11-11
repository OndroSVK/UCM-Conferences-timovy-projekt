<?php

use App\Modules\Conferences\Enums\SubmissionRequirement;
use App\Modules\Registration\Actions\CreateRegistration;
use App\Modules\Registration\Actions\SetFeeWaived;
use App\Modules\Submissions\Actions\InitializeSubmission;
use App\Modules\Submissions\Actions\MarkDecisionNotified;
use App\Modules\Submissions\Actions\RecordCommitteeDecision;
use App\Modules\Submissions\Actions\SubmitSubmission;
use App\Modules\Submissions\Enums\SubmissionType;
use App\Modules\Submissions\Models\Decision;
use App\Modules\Workflow\Enums\SubmissionStatus;
use App\Modules\Workflow\Exceptions\InvalidStatusTransition;
use App\Shared\Auth\Role;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\In;

// Internal steps called by other features: CreateRegistration, SetFeeWaived,
// InitializeSubmission, MarkDecisionNotified. Their main paths are covered by the callers' tests.

it('CreateRegistration builds rules from the categories and options of the conference', function () {
    $conference = makeConference(2027);

    $rules = app(CreateRegistration::class)->rulesFor($conference);

    $in = (string) collect($rules['category'])->first(fn ($r) => $r instanceof In);

    expect($in)->toContain('"speaker"')->toContain('"poster_author"')->toContain('"passive_participant"')
        ->and($rules['industry'][0])->toBe('required');
});

it('SetFeeWaived writes only the property', function () {
    $conference = makeConference(2027);
    ['registration' => $registration] = registerParticipant($conference);
    inConference($conference);

    app(SetFeeWaived::class)->handle($registration, 'Guest');

    $fresh = freshRegistration($registration);
    expect($fresh->fee_waived)->toBeTrue()
        ->and($fresh->fee_waiver_note)->toBe('Guest')
        ->and($fresh->payment_status->value)->toBe('pending'); // status only via ChangeStatus
});

it('InitializeSubmission maps the requirement to type and status, only once', function () {
    $conference = makeConference(2027);
    ['registration' => $registration] = registerParticipant($conference, 'poster_author');
    inConference($conference);

    $submission = $registration->submissions()->first();
    expect($submission->type)->toBe(SubmissionType::Poster)
        ->and($submission->status)->toBe(SubmissionStatus::Draft);

    // unique per registration in the prototype
    expect(fn () => app(InitializeSubmission::class)->handle($registration, SubmissionRequirement::Abstract))
        ->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});

it('MarkDecisionNotified sets the time once', function () {
    Storage::fake('local');
    \Illuminate\Support\Facades\Mail::shouldReceive('to')->andThrow(new RuntimeException('down')); // decision mail fails
    $conference = makeConference(2027);
    ['user' => $author, 'registration' => $registration] = registerParticipant($conference);
    inConference($conference);
    $submission = $registration->submissions()->first();
    app(SubmitSubmission::class)->handle($author, $submission, ['title' => 'P', 'file' => fakePdf()]);
    $decision = app(RecordCommitteeDecision::class)->handle(staff($conference, Role::ProgrammeCommittee), $submission->fresh(), [
        'decision' => 'rejected', 'justification' => 'Out of scope.',
    ]);
    expect(Decision::query()->find($decision->id)->notified_at)->toBeNull(); // mail failed → not notified

    app(MarkDecisionNotified::class)->handle($decision->id);
    $first = Decision::query()->find($decision->id)->notified_at;
    $this->travel(1)->hours();
    app(MarkDecisionNotified::class)->handle($decision->id);

    expect($first)->not->toBeNull()
        ->and(Decision::query()->find($decision->id)->notified_at->equalTo($first))->toBeTrue();
});
