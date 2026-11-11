<?php

use App\Modules\Conferences\Actions\ResolveConferenceFromHost;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

it('resolves the conference from the subdomain and sets the context', function (string $host) {
    $conference = makeConference(2027);

    $resolved = app(ResolveConferenceFromHost::class)->handle($host);

    expect($resolved->id)->toBe($conference->id)
        ->and(conferenceContext()->conferenceId())->toBe($conference->id);
})->with(['ans2027.ucm.sk', 'ans2027.localhost', 'ANS2027.localhost']);

it('returns 404 for an unknown subdomain', function () {
    makeConference(2027);

    app(ResolveConferenceFromHost::class)->handle('ans1999.ucm.sk');
})->throws(NotFoundHttpException::class);
