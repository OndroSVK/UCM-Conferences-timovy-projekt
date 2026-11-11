<?php

use App\Modules\Auth\Actions\ChangeEmail;
use App\Modules\Auth\Actions\CreateUserAccount;
use App\Modules\Auth\Actions\UpdateAccountName;
use App\Shared\Auth\AccessControl;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

// Helper features of Auth used by other modules: CreateUserAccount, ChangeEmail, UpdateAccountName.

it('creates a participant account with a normalized e-mail', function () {
    $user = app(CreateUserAccount::class)->handle(['name' => 'Jana Testová', 'email' => ' Jana@Example.TEST ', 'password' => 'secret-password']);

    expect($user->email)->toBe('jana@example.test')
        ->and(app(AccessControl::class)->isParticipant($user))->toBeTrue();
});

it('refuses a duplicate e-mail', function () {
    makeUser(['email' => 'jana@example.test']);

    app(CreateUserAccount::class)->handle(['name' => 'Jana', 'email' => 'jana@example.test', 'password' => 'secret-password']);
})->throws(ValidationException::class);

it('a changed e-mail must be verified again', function () {
    Notification::fake();
    $user = makeUser(['email' => 'old@example.test']);

    app(ChangeEmail::class)->handle($user, 'new@example.test');

    expect($user->fresh()->email)->toBe('new@example.test')
        ->and($user->fresh()->hasVerifiedEmail())->toBeFalse();
    Notification::assertSentTo($user, VerifyEmailNotification::class);
});

it('refuses an e-mail of another account', function () {
    makeUser(['email' => 'taken@example.test']);

    app(ChangeEmail::class)->handle(makeUser(), 'taken@example.test');
})->throws(ValidationException::class);

it('updates the display name', function () {
    $user = makeUser(['name' => 'Old']);

    app(UpdateAccountName::class)->handle($user, 'New Name');

    expect($user->fresh()->name)->toBe('New Name');
});
