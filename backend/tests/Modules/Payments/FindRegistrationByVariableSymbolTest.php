<?php

use App\Modules\Payments\Actions\FindRegistrationByVariableSymbol;
use App\Shared\Auth\Role;
use App\Shared\Conference\MissingConferenceContext;
use Illuminate\Auth\Access\AuthorizationException;

it('finds a registration by VS in the active conference only', function () {
    $ans27 = makeConference(2027);
    $ans28 = makeConference(2028);
    ['registration' => $r27] = registerParticipant($ans27);
    ['registration' => $r28] = registerParticipant($ans28);
    $organizer = staff($ans27, Role::Organizer);
    staff($ans28, Role::Organizer, user: $organizer);
    $find = app(FindRegistrationByVariableSymbol::class);

    inConference($ans27);
    expect($find->handle($organizer, ' 27 000 001 ')?->id)->toBe($r27->id)
        ->and($find->handle($organizer, $r28->variable_symbol))->toBeNull();
});

it('requires an active conference', function () {
    app(FindRegistrationByVariableSymbol::class)->handle(superadmin(), '27000001');
})->throws(MissingConferenceContext::class);

it('is not available to the programme committee', function () {
    $ans27 = inConference(makeConference(2027));

    app(FindRegistrationByVariableSymbol::class)->handle(staff($ans27, Role::ProgrammeCommittee), '27000001');
})->throws(AuthorizationException::class);
