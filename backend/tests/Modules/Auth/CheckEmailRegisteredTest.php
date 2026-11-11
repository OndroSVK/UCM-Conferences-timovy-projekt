<?php

use App\Modules\Auth\Actions\CheckEmailRegistered;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

beforeEach(fn () => RateLimiter::clear('email-check:'.sha1('10.0.0.1')));

// US-04
it('answers only yes or no', function () {
    makeUser(['email' => 'known@example.test']);
    $check = app(CheckEmailRegistered::class);

    expect($check->handle(['email' => 'KNOWN@example.test'], '10.0.0.1'))->toBeTrue()
        ->and($check->handle(['email' => 'unknown@example.test'], '10.0.0.1'))->toBeFalse();
});

it('is rate limited per IP', function () {
    $check = app(CheckEmailRegistered::class);

    foreach (range(1, (int) config('conferences.email_check.max_attempts')) as $_) {
        $check->handle(['email' => 'a@example.test'], '10.0.0.1');
    }

    $check->handle(['email' => 'a@example.test'], '10.0.0.1');
})->throws(ValidationException::class);
