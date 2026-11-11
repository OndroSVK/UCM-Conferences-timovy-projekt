<?php

namespace App\Modules\Notifications\Listeners;

use App\Modules\Notifications\Enums\EmailType;
use App\Modules\Notifications\Support\ConferenceMail;
use App\Modules\Notifications\Support\MailSender;
use App\Modules\Registration\Models\Registration;
use App\Modules\Workflow\Enums\RegistrationStatus;
use App\Modules\Workflow\Enums\StatusCategory;
use App\Modules\Workflow\Events\StatusChanged;
use App\Shared\Conference\ConferenceContext;
use Illuminate\Contracts\Queue\ShouldQueue;

/** received → confirmed: confirmation of participation. */
final class SendParticipationConfirmed implements ShouldQueue
{
    public bool $afterCommit = true;

    public function handle(StatusChanged $event): void
    {
        if (! $event->is(StatusCategory::Registration, RegistrationStatus::Confirmed->value)) {
            return;
        }

        app(ConferenceContext::class)->runForConference($event->conferenceId, function () use ($event) {
            $registration = Registration::query()->with(['conference', 'profile', 'user'])->find($event->registrationId);
            if ($registration === null) {
                return;
            }

            app(MailSender::class)->send($registration, EmailType::ParticipationConfirmed, new ConferenceMail(
                __('mail.participation_confirmed.subject', ['conference' => $registration->conference->name]),
                'participation-confirmed',
                ['registration' => $registration, 'conference' => $registration->conference],
            ));
        });
    }
}
