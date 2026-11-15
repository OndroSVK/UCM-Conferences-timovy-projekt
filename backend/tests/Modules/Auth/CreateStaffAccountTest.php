<?php

use App\Modules\Auth\Actions\CreateStaffAccount;
use App\Shared\Auth\AccessControl;
use App\Shared\Auth\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

$input = ['name' => 'Olga Organizer', 'email' => 'Olga@Example.TEST', 'password' => 'secret-password'];

it('creates a verified staff account without participant or superadmin role', function () use ($input) {
    $user = app(CreateStaffAccount::class)->handle($input);
    $access = app(AccessControl::class);

    expect($user->email)->toBe('olga@example.test')
        ->and($user->hasVerifiedEmail())->toBeTrue()
        ->and($access->isParticipant($user))->toBeFalse()
        ->and($access->isSuperadmin($user))->toBeFalse()
        ->and($access->isStaff($user))->toBeFalse(); // until assigned to a conference
});

it('creates a superadmin from the console (no actor)', function () use ($input) {
    $user = app(CreateStaffAccount::class)->handle($input, superadmin: true);

    expect(app(AccessControl::class)->isSuperadmin($user))->toBeTrue();
});

it('lets only a superadmin create accounts from the UI', function () use ($input) {
    $conference = makeConference(2027);

    expect(app(CreateStaffAccount::class)->handle($input, superadmin())->exists)->toBeTrue();

    app(CreateStaffAccount::class)->handle(['email' => 'second@example.test'] + $input, staff($conference, Role::Organizer));
})->throws(AuthorizationException::class);

it('refuses a duplicate e-mail and a short password', function (array $override) use ($input) {
    makeUser(['email' => 'taken@example.test']);

    app(CreateStaffAccount::class)->handle($override + $input);
})->with([
    [['email' => 'taken@example.test']],
    [['password' => 'short']],
])->throws(ValidationException::class);
