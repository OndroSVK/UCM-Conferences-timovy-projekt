<?php

use App\Modules\Conferences\Actions\SwitchActiveConference;
use App\Shared\Auth\Role;
use Illuminate\Auth\Access\AuthorizationException;

it('switches to an assigned conference', function () {
    $ans27 = makeConference(2027);
    $organizer = staff($ans27, Role::Organizer);

    app(SwitchActiveConference::class)->handle($organizer, $ans27->id);

    expect(conferenceContext()->conferenceId())->toBe($ans27->id);
});

// BR4
it('refuses a conference that is not assigned', function () {
    $ans27 = makeConference(2027);
    $ans28 = makeConference(2028);
    $organizer = staff($ans27, Role::Organizer);

    app(SwitchActiveConference::class)->handle($organizer, $ans28->id);
})->throws(AuthorizationException::class);

it('lets the superadmin switch to any conference', function () {
    makeConference(2027);
    $ans28 = makeConference(2028);

    app(SwitchActiveConference::class)->handle(superadmin(), $ans28->id);

    expect(conferenceContext()->conferenceId())->toBe($ans28->id);
});

it('refuses a participant', function () {
    $ans27 = makeConference(2027);
    ['user' => $participant] = registerParticipant($ans27);

    app(SwitchActiveConference::class)->handle($participant, $ans27->id);
})->throws(AuthorizationException::class);
