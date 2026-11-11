<?php

namespace App\Modules\Auth\Actions;

use App\Shared\Actions\Action;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Forgotten password (Laravel password broker). Always the same answer, so the
 * response does not reveal whether the account exists.
 */
final class SendPasswordResetLink extends Action
{
    public static function rules(): array
    {
        return ['email' => ['required', 'string', 'email']];
    }

    /**
     * @param  array{email:string}  $input
     */
    public function handle(array $input): void
    {
        $data = $this->validate($input);

        Password::broker()->sendResetLink(['email' => Str::lower(trim($data['email']))]);
    }
}
