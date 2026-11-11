<?php

use App\Modules\Submissions\Actions\RecordCommitteeDecision;
use App\Modules\Submissions\Actions\ReplaceSubmissionFile;
use App\Modules\Submissions\Actions\SubmitSubmission;
use App\Modules\Submissions\Models\SubmissionVersion;
use App\Modules\Workflow\Enums\SubmissionStatus;
use App\Modules\Workflow\Models\StatusHistory;
use App\Shared\Actions\DomainException;
use App\Shared\Auth\Role;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->conference = makeConference(2027);
    ['user' => $this->author, 'registration' => $registration] = registerParticipant($this->conference, 'poster_author');
    inConference($this->conference);
    $this->submission = $registration->submissions()->first();
    app(SubmitSubmission::class)->handle($this->author, $this->submission, ['title' => 'Poster', 'file' => fakePdf()]);
});

// PROPOSAL O31
it('adds version 2, keeps version 1 and records the change', function () {
    $v2 = app(ReplaceSubmissionFile::class)->handle($this->author, $this->submission->fresh(), ['file' => fakePdf('v2.pdf')]);

    expect($v2->version)->toBe(2)
        ->and(SubmissionVersion::query()->where('submission_id', $this->submission->id)->count())->toBe(2)
        ->and($this->submission->fresh()->status)->toBe(SubmissionStatus::Submitted)
        ->and(StatusHistory::query()->where('note', 'New version 2')->exists())->toBeTrue();
});

it('refuses a new version after the committee decided', function () {
    app(RecordCommitteeDecision::class)->handle(
        staff($this->conference, Role::ProgrammeCommittee),
        $this->submission->fresh(),
        ['decision' => 'accepted', 'justification' => 'Good'],
    );

    app(ReplaceSubmissionFile::class)->handle($this->author, $this->submission->fresh(), ['file' => fakePdf()]);
})->throws(DomainException::class);

it('refuses after the deadline', function () {
    $this->conference->update(['submission_deadline' => now()->subDay()->toDateString()]);

    app(ReplaceSubmissionFile::class)->handle($this->author, $this->submission->fresh(), ['file' => fakePdf()]);
})->throws(DomainException::class);
