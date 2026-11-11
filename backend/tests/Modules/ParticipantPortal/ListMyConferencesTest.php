<?php

use App\Modules\ParticipantPortal\Actions\ListMyConferences;
use App\Modules\Registration\Actions\RegisterExistingUserForConference;

// US-08
it('lists own registrations, newest conference first, regardless of the subdomain', function () {
    $ans27 = makeConference(2027, ['starts_on' => now()->addYear()->toDateString()]);
    $ans28 = makeConference(2028, ['starts_on' => now()->addYears(2)->toDateString()]);
    ['user' => $user] = registerParticipant($ans27);
    app(RegisterExistingUserForConference::class)->handle($user, $ans28, registrationInput(['category' => 'passive_participant']));
    registerParticipant($ans28); // someone else

    inConference($ans27); // e.g. the user came via ans2027.ucm.sk

    $list = app(ListMyConferences::class)->handle($user);

    expect($list)->toHaveCount(2)
        ->and($list->pluck('conference.subdomain')->all())->toBe(['ans2028', 'ans2027'])
        ->and($list->every(fn ($r) => $r->user_id === $user->id))->toBeTrue()
        ->and(conferenceContext()->conferenceId())->toBe($ans27->id); // context restored
});
