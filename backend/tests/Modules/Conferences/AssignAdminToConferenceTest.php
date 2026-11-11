<?php

use App\Modules\Conferences\Actions\AssignAdminToConference;
use App\Shared\Actions\DomainException;
use App\Shared\Auth\AccessControl;
use App\Shared\Auth\Permission;
use App\Shared\Auth\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

it('superadmin assigns an organizer with delegated payments to one conference only', function () {
    $ans27 = makeConference(2027);
    $ans28 = makeConference(2028);
    $user = makeUser();

    app(AssignAdminToConference::class)->handle(superadmin(), $ans27, [
        'user_id' => $user->id,
        'role' => 'organizer',
        'extra_permissions' => ['payments.record'],
    ]);

    $access = app(AccessControl::class);
    expect($access->can($user, Permission::PaymentsRecord, $ans27->id))->toBeTrue()
        ->and($access->can($user, Permission::PaymentsRecord, $ans28->id))->toBeFalse()
        ->and($access->canAccessConference($user, $ans28->id))->toBeFalse();
});

// BR8: a role in one conference does not leak to another
it('keeps an organizer in 2027 without rights in 2028 even when assigned there as committee', function () {
    $ans27 = makeConference(2027);
    $ans28 = makeConference(2028);
    $user = paymentsOrganizer($ans27);
    staff($ans28, Role::ProgrammeCommittee, user: $user);

    $access = app(AccessControl::class);
    expect($access->can($user, Permission::PaymentsRecord, $ans28->id))->toBeFalse()
        ->and($access->can($user, Permission::RegistrationsViewFinance, $ans28->id))->toBeFalse()
        ->and($access->can($user, Permission::SubmissionsDecide, $ans28->id))->toBeTrue()
        ->and($access->can($user, Permission::SubmissionsDecide, $ans27->id))->toBeFalse();
});

it('refuses delegation to the programme committee', function () {
    $ans27 = makeConference(2027);

    app(AssignAdminToConference::class)->handle(superadmin(), $ans27, [
        'user_id' => makeUser()->id,
        'role' => 'programme_committee',
        'extra_permissions' => ['payments.record'],
    ]);
})->throws(DomainException::class);

it('refuses non-delegable permissions', function () {
    $ans27 = makeConference(2027);

    app(AssignAdminToConference::class)->handle(superadmin(), $ans27, [
        'user_id' => makeUser()->id,
        'role' => 'organizer',
        'extra_permissions' => ['submissions.decide'],
    ]);
})->throws(ValidationException::class);

// PROPOSAL BO8
it('only the superadmin assigns', function () {
    $ans27 = makeConference(2027);
    $organizer = paymentsOrganizer($ans27);

    app(AssignAdminToConference::class)->handle($organizer, $ans27, ['user_id' => makeUser()->id, 'role' => 'organizer']);
})->throws(AuthorizationException::class);

it('revokes a role', function () {
    $ans27 = makeConference(2027);
    $organizer = staff($ans27, Role::Organizer);

    app(AssignAdminToConference::class)->revoke(superadmin(), $ans27, $organizer->id, Role::Organizer);

    expect(app(AccessControl::class)->canAccessConference($organizer, $ans27->id))->toBeFalse();
});
