<?php

use App\Modules\Conferences\Actions\ListAccessibleConferences;
use App\Shared\Auth\Role;

it('lists only assigned conferences for staff', function () {
    $ans27 = makeConference(2027);
    makeConference(2028);
    $committee = staff($ans27, Role::ProgrammeCommittee);

    expect(app(ListAccessibleConferences::class)->handle($committee)->pluck('id')->all())->toBe([$ans27->id]);
});

it('lists all conferences for the superadmin, newest first', function () {
    $ans27 = makeConference(2027, ['starts_on' => now()->addYear()->toDateString()]);
    $ans28 = makeConference(2028, ['starts_on' => now()->addYears(2)->toDateString()]);

    expect(app(ListAccessibleConferences::class)->handle(superadmin())->pluck('id')->all())->toBe([$ans28->id, $ans27->id]);
});

it('lists nothing for a participant', function () {
    $ans27 = makeConference(2027);
    ['user' => $participant] = registerParticipant($ans27);

    expect(app(ListAccessibleConferences::class)->handle($participant))->toBeEmpty();
});
