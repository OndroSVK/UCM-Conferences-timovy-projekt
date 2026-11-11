<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Models\User;
use App\Shared\Actions\Action;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

/**
 * New password from a reset token.
 */
final class ResetPassword extends Action
{
    public static function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', PasswordRule::min(8)],
        ];
    }

    /**
     * @param  array{token:string, email:string, password:string, password_confirmation:string}  $input
     */
    public function handle(array $input): void
    {
        $data = $this->validate($input);

        $status = Password::broker()->reset(
            [
                'email' => Str::lower(trim($data['email'])),
                'password' => $data['password'],
                'password_confirmation' => $input['password_confirmation'] ?? null,
                'token' => $data['token'],
            ],
            function (User $user, string $password): void {
                $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }
    }
}
