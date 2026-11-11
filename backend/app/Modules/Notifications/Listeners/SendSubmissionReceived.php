<?php

namespace App\Modules\Notifications\Listeners;

use App\Modules\Notifications\Enums\EmailType;
use App\Modules\Notifications\Support\ConferenceMail;
use App\Modules\Notifications\Support\MailSender;
use App\Modules\Registration\Models\Registration;
use App\Modules\Submissions\Events\SubmissionSubmitted;
use App\Modules\Submissions\Models\Submission;
use App\Shared\Conference\ConferenceContext;
use Illuminate\Contracts\Queue\ShouldQueue;

/** Confirmation of a submission or of its new version. */
final class SendSubmissionReceived implements ShouldQueue
{
    public bool $afterCommit = true;

    public function handle(SubmissionSubmitted $event): void
    {
        app(ConferenceContext::class)->runForConference($event->conferenceId, function () use ($event) {
            $registration = Registration::query()->with(['conference', 'profile', 'user'])->find($event->registrationId);
            $submission = Submission::query()->find($event->submissionId);
            if ($registration === null || $submission === null) {
                return;
            }

            app(MailSender::class)->send($registration, EmailType::SubmissionReceived, new ConferenceMail(
                __('mail.submission_received.subject', ['conference' => $registration->conference->name]),
                'submission-received',
                [
                    'registration' => $registration,
                    'conference' => $registration->conference,
                    'submission' => $submission,
                    'version' => $event->version,
                ],
            ));
        });
    }
}
