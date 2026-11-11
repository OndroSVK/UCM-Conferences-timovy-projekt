<?php

use App\Modules\Auth\Actions\AuthenticateUser;
use App\Modules\Auth\Enums\LoginDestination;
use App\Shared\Auth\Role;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

beforeEach(fn () => RateLimiter::clear('login:'.sha1('user@example.test|127.0.0.1')));

// US-02
it('logs in a participant and sends them to the user page', function () {
    $conference = makeConference(2027);
    registerParticipant($conference, 'speaker', ['email' => 'user@example.test']);

    $result = app(AuthenticateUser::class)->handle(['email' => 'user@example.test', 'password' => 'secret-password'], '127.0.0.1');

    expect($result['destination'])->toBe(LoginDestination::ParticipantPortal);
});

it('sends an organizer to the admin', function () {
    $conference = makeConference(2027);
    staff($conference, Role::Organizer, user: makeUser(['email' => 'user@example.test']));

    $result = app(AuthenticateUser::class)->handle(['email' => 'user@example.test', 'password' => 'password'], '127.0.0.1');

    expect($result['destination'])->toBe(LoginDestination::Admin);
});

it('rejects a wrong password with a generic message', function () {
    makeUser(['email' => 'user@example.test']);

    try {
        app(AuthenticateUser::class)->handle(['email' => 'user@example.test', 'password' => 'wrong'], '127.0.0.1');
        $this->fail('Expected a validation error.');
    } catch (ValidationException $e) {
        expect($e->errors()['email'][0])->toBe(__('auth.failed'));
    }
});

// PROPOSAL BO2: 3 failed attempts, then lockout
it('locks the login after 3 failed attempts, even for the correct password', function () {
    makeUser(['email' => 'user@example.test']);
    $login = fn (string $password) => app(AuthenticateUser::class)->handle(['email' => 'user@example.test', 'password' => $password], '127.0.0.1');

    foreach (range(1, 3) as $_) {
        expect(fn () => $login('wrong'))->toThrow(ValidationException::class, __('auth.failed'));
    }

    expect(fn () => $login('password'))->toThrow(ValidationException::class);
    expect(RateLimiter::tooManyAttempts('login:'.sha1('user@example.test|127.0.0.1'), 3))->toBeTrue();
});

it('resets the counter after a successful login', function () {
    makeUser(['email' => 'user@example.test']);
    $login = fn (string $password) => app(AuthenticateUser::class)->handle(['email' => 'user@example.test', 'password' => $password], '127.0.0.1');

    expect(fn () => $login('wrong'))->toThrow(ValidationException::class);
    expect(fn () => $login('wrong'))->toThrow(ValidationException::class);
    $login('password');

    expect(RateLimiter::attempts('login:'.sha1('user@example.test|127.0.0.1')))->toBe(0);
});
