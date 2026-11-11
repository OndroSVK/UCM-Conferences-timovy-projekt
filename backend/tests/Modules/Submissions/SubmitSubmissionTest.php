<?php

use App\Modules\Submissions\Actions\SubmitSubmission;
use App\Modules\Submissions\Events\SubmissionSubmitted;
use App\Modules\Workflow\Enums\SubmissionStatus;
use App\Shared\Actions\DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    Storage::fake('local');
    $this->conference = makeConference(2027);
    ['user' => $this->author, 'registration' => $this->registration] = registerParticipant($this->conference, 'speaker');
    inConference($this->conference);
    $this->submission = $this->registration->submissions()->first();
});

it('stores version 1 privately and moves draft → submitted', function () {
    Event::fake([SubmissionSubmitted::class]);

    $version = app(SubmitSubmission::class)->handle($this->author, $this->submission, ['title' => 'Quantum dots', 'file' => fakePdf()]);

    expect($version->version)->toBe(1)
        ->and($this->submission->fresh()->status)->toBe(SubmissionStatus::Submitted)
        ->and($this->submission->fresh()->title)->toBe('Quantum dots')
        ->and(str_starts_with($version->path, 'submissions/'))->toBeTrue();
    Storage::disk('local')->assertExists($version->path);
    Event::assertDispatched(SubmissionSubmitted::class, fn ($e) => $e->version === 1);
});

// R14, backend.md 11.7
it('refuses a fake PDF (text file with .pdf extension)', function () {
    $fake = UploadedFile::fake()->createWithContent('paper.pdf', 'just text, not a pdf');

    app(SubmitSubmission::class)->handle($this->author, $this->submission, ['title' => 'X', 'file' => $fake]);
})->throws(ValidationException::class);

it('refuses another file type', function () {
    $image = UploadedFile::fake()->image('paper.png');

    app(SubmitSubmission::class)->handle($this->author, $this->submission, ['title' => 'X', 'file' => $image]);
})->throws(ValidationException::class);

it('refuses a file over the size limit', function () {
    config(['conferences.submissions.max_kilobytes' => 1]);
    $big = UploadedFile::fake()->createWithContent('paper.pdf', '%PDF-1.4'.str_repeat('x', 4096));

    app(SubmitSubmission::class)->handle($this->author, $this->submission, ['title' => 'X', 'file' => $big]);
})->throws(ValidationException::class);

it('refuses after the submission deadline', function () {
    $this->conference->update(['submission_deadline' => now()->subDay()->toDateString()]);

    app(SubmitSubmission::class)->handle($this->author, $this->submission, ['title' => 'X', 'file' => fakePdf()]);
})->throws(DomainException::class);

it('refuses another participant', function () {
    ['user' => $other] = registerParticipant($this->conference);

    app(SubmitSubmission::class)->handle($other, $this->submission, ['title' => 'X', 'file' => fakePdf()]);
})->throws(AuthorizationException::class);

it('refuses a second submit; a new version goes through ReplaceSubmissionFile', function () {
    app(SubmitSubmission::class)->handle($this->author, $this->submission, ['title' => 'X', 'file' => fakePdf()]);
    app(SubmitSubmission::class)->handle($this->author, $this->submission->fresh(), ['title' => 'X', 'file' => fakePdf()]);
})->throws(DomainException::class);
