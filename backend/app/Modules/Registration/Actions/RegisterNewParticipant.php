<?php

namespace App\Modules\Registration\Actions;

use App\Modules\Auth\Actions\CreateUserAccount;
use App\Modules\Auth\Models\User;
use App\Modules\Conferences\Models\Conference;
use App\Modules\Registration\Models\Registration;
use App\Shared\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

/**
 * US-03: in one transaction account + profile + registration. An existing e-mail
 * creates no second account; the UI offers login instead (US-04).
 */
final class RegisterNewParticipant extends Action
{
    public function __construct(
        private readonly CreateUserAccount $createAccount,
        private readonly UpdateParticipantProfile $updateProfile,
        private readonly CreateRegistration $createRegistration,
    ) {
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{user: User, registration: Registration}
     */
    public function handle(Conference $conference, array $input): array
    {
        $input['email'] = Str::lower(trim((string) ($input['email'] ?? '')));

        $rules = array_merge(
            [
                'email' => ['required', 'string', 'email', 'max:255'],
                'password' => ['required', 'string', 'confirmed', PasswordRule::min(8)],
            ],
            UpdateParticipantProfile::rules(),
            $this->createRegistration->rulesFor($conference),
        );

        $data = Validator::make($input, $rules)->validate();

        if (User::query()->where('email', $data['email'])->exists()) {
            throw ValidationException::withMessages(['email' => __('registration.email_exists')]);
        }

        $result = DB::transaction(function () use ($conference, $data) {
            $user = $this->createAccount->handle([
                'name' => $data['first_name'].' '.$data['last_name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $this->updateProfile->handle($user, $data);
            $registration = $this->createRegistration->handle($user, $conference, $data, newAccount: true);

            return ['user' => $user, 'registration' => $registration];
        });

        // Verification link (does not block access to the user page). A mail failure
        // never undoes the registration (backend.md 4.6).
        rescue(fn () => $result['user']->sendEmailVerificationNotification(), report: true);

        return $result;
    }
}
