<?php

use App\Modules\Auth\Actions\CreateUserAccount;
use App\Modules\Auth\Actions\ResolvePostLoginDestination;
use App\Modules\Auth\Enums\LoginDestination;
use App\Shared\Auth\Role;

it('resolves the destination by roles (R11, R17)', function () {
    $conference = makeConference(2027);
    $resolve = app(ResolvePostLoginDestination::class);

    ['user' => $participant] = registerParticipant($conference);
    $organizer = staff($conference, Role::Organizer);
    $both = staff($conference, Role::ProgrammeCommittee);
    app(CreateUserAccount::class)->ensureParticipantRole($both);

    expect($resolve->handle($participant))->toBe(LoginDestination::ParticipantPortal)
        ->and($resolve->handle($organizer))->toBe(LoginDestination::Admin)
        ->and($resolve->handle(superadmin()))->toBe(LoginDestination::Admin)
        ->and($resolve->handle($both))->toBe(LoginDestination::Choose);
});
