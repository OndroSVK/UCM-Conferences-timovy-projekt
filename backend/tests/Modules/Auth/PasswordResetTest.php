<?php

use App\Modules\Auth\Actions\ResetPassword;
use App\Modules\Auth\Actions\SendPasswordResetLink;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

// Covers SendPasswordResetLink and ResetPassword (one flow).

it('sends a reset link to an existing account', function () {
    Notification::fake();
    $user = makeUser(['email' => 'user@example.test']);

    app(SendPasswordResetLink::class)->handle(['email' => 'user@example.test']);

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

it('answers the same way for an unknown e-mail', function () {
    Notification::fake();

    app(SendPasswordResetLink::class)->handle(['email' => 'nobody@example.test']);

    Notification::assertNothingSent();
});

it('sets a new password from a valid token', function () {
    $user = makeUser(['email' => 'user@example.test']);
    $token = Password::broker()->createToken($user);

    app(ResetPassword::class)->handle([
        'token' => $token,
        'email' => 'user@example.test',
        'password' => 'new-password-1',
        'password_confirmation' => 'new-password-1',
    ]);

    expect(Hash::check('new-password-1', $user->fresh()->password))->toBeTrue();
});

it('refuses an invalid token', function () {
    makeUser(['email' => 'user@example.test']);

    app(ResetPassword::class)->handle([
        'token' => 'invalid',
        'email' => 'user@example.test',
        'password' => 'new-password-1',
        'password_confirmation' => 'new-password-1',
    ]);
})->throws(ValidationException::class);
