<?php

namespace App\Modules\Notifications\Listeners;

use App\Modules\Notifications\Enums\EmailType;
use App\Modules\Notifications\Support\ConferenceMail;
use App\Modules\Notifications\Support\MailSender;
use App\Modules\ParticipantPortal\Actions\GetPaymentInstructions;
use App\Modules\Registration\Events\RegistrationCreated;
use App\Modules\Registration\Models\Registration;
use App\Shared\Conference\ConferenceContext;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * RegistrationCreated → confirmation; for a paid category also the payment details (US-03).
 */
final class SendRegistrationConfirmation implements ShouldQueue
{
    public bool $afterCommit = true;

    public function handle(RegistrationCreated $event): void
    {
        app(ConferenceContext::class)->runForConference($event->conferenceId, function () use ($event) {
            $registration = Registration::query()->with(['conference', 'profile', 'user'])->find($event->registrationId);
            if ($registration === null) {
                return;
            }

            $instructions = app(GetPaymentInstructions::class)->handle($registration->user, $registration);

            app(MailSender::class)->send($registration, EmailType::RegistrationConfirmation, new ConferenceMail(
                __('mail.registration_confirmation.subject', ['conference' => $registration->conference->name]),
                'registration-confirmation',
                ['registration' => $registration, 'conference' => $registration->conference, 'instructions' => $instructions],
            ));
        });
    }
}
