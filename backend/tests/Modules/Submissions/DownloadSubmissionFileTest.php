<?php

use App\Modules\Submissions\Actions\DownloadSubmissionFile;
use App\Modules\Submissions\Actions\SubmitSubmission;
use App\Shared\Auth\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->conference = makeConference(2027);
    ['user' => $this->author, 'registration' => $registration] = registerParticipant($this->conference, 'speaker');
    inConference($this->conference);
    $this->version = app(SubmitSubmission::class)->handle($this->author, $registration->submissions()->first(), ['title' => 'Paper', 'file' => fakePdf()]);
});

it('allows the author, organizer, committee and superadmin', function () {
    $download = app(DownloadSubmissionFile::class);

    foreach ([
        $this->author,
        staff($this->conference, Role::Organizer),
        staff($this->conference, Role::ProgrammeCommittee),
        superadmin(),
    ] as $actor) {
        $file = $download->handle($actor, $this->version);
        expect($file->path)->toBe($this->version->path)
            ->and($file->mimeType)->toBe('application/pdf');
    }
});

it('refuses another participant (also via a direct link)', function () {
    ['user' => $other] = registerParticipant($this->conference);

    app(DownloadSubmissionFile::class)->handle($other, $this->version);
})->throws(AuthorizationException::class);

it('refuses staff of another conference', function () {
    app(DownloadSubmissionFile::class)->handle(staff(makeConference(2028), Role::ProgrammeCommittee), $this->version);
})->throws(AuthorizationException::class);
