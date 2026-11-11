<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Enums\LoginDestination;
use App\Modules\Auth\Models\User;
use App\Shared\Actions\Action;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Checks e-mail + password and returns the user and the destination (R11, R17, US-02).
 * The HTTP layer starts the session.
 *
 * PROPOSAL: BO2 — at most 3 failed attempts per e-mail and IP, then a lockout
 * (config conferences.login). Working value; to be confirmed with the team.
 */
final class AuthenticateUser extends Action
{
    public function __construct(private readonly ResolvePostLoginDestination $destination)
    {
    }

    public static function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @param  array{email:string, password:string}  $input
     * @return array{user: User, destination: LoginDestination}
     *
     * @throws ValidationException
     */
    public function handle(array $input, string $ip): array
    {
        $data = $this->validate($input);
        $email = Str::lower(trim($data['email']));
        $key = 'login:'.sha1($email.'|'.$ip);
        $maxAttempts = (int) config('conferences.login.max_attempts');

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw ValidationException::withMessages([
                'email' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($key)]),
            ]);
        }

        $user = User::query()->where('email', $email)->first();

        if ($user === null || ! Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($key, (int) config('conferences.login.decay_seconds'));

            // Same message for unknown e-mail and wrong password; the UI offers password reset (US-02).
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        RateLimiter::clear($key);

        return ['user' => $user, 'destination' => $this->destination->handle($user)];
    }
}
