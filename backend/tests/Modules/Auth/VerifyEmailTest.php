<?php

use App\Modules\Auth\Actions\VerifyEmail;
use Illuminate\Auth\Access\AuthorizationException;

it('verifies the e-mail with the matching hash', function () {
    $user = makeUser(['email' => 'user@example.test', 'email_verified_at' => null]);

    app(VerifyEmail::class)->handle($user, sha1('user@example.test'));

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('refuses a hash of another e-mail', function () {
    $user = makeUser(['email' => 'user@example.test', 'email_verified_at' => null]);

    app(VerifyEmail::class)->handle($user, sha1('old@example.test'));
})->throws(AuthorizationException::class);
