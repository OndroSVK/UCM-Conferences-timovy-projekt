<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Models\User;
use App\Shared\Actions\Action;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;

/**
 * E-mail verification from the signed link (the HTTP layer checks the signature).
 * The hash must match the current e-mail, so an old link cannot verify a changed address.
 */
final class VerifyEmail extends Action
{
    public function handle(User $user, string $hash): void
    {
        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            throw new AuthorizationException(__('errors.invalid_verification_link'));
        }

        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }
    }

    /** Sends a new verification link. */
    public function resend(User $user): void
    {
        if (! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }
    }
}
