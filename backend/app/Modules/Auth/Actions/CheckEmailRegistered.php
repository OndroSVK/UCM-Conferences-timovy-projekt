<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Models\User;
use App\Shared\Actions\Action;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Whether the e-mail already has an account (US-04): answer yes/no only.
 * It reveals account existence, therefore it is rate limited per IP.
 */
final class CheckEmailRegistered extends Action
{
    public static function rules(): array
    {
        return ['email' => ['required', 'string', 'email']];
    }

    /**
     * @param  array{email:string}  $input
     */
    public function handle(array $input, string $ip): bool
    {
        $data = $this->validate($input);
        $key = 'email-check:'.sha1($ip);

        if (RateLimiter::tooManyAttempts($key, (int) config('conferences.email_check.max_attempts'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($key)]),
            ]);
        }

        RateLimiter::hit($key, (int) config('conferences.email_check.decay_seconds'));

        return User::query()->where('email', Str::lower(trim($data['email'])))->exists();
    }
}
