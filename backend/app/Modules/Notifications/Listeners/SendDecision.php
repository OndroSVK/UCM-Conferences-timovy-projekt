<?php

namespace App\Modules\Notifications\Listeners;

use App\Modules\Notifications\Enums\EmailType;
use App\Modules\Notifications\Support\ConferenceMail;
use App\Modules\Notifications\Support\MailSender;
use App\Modules\Registration\Models\Registration;
use App\Modules\Submissions\Actions\MarkDecisionNotified;
use App\Modules\Submissions\Events\CommitteeDecisionRecorded;
use App\Modules\Submissions\Models\Decision;
use App\Shared\Conference\ConferenceContext;
use Illuminate\Contracts\Queue\ShouldQueue;

/** Committee decision and the justification meant for the author. */
final class SendDecision implements ShouldQueue
{
    public bool $afterCommit = true;

    public function handle(CommitteeDecisionRecorded $event): void
    {
        app(ConferenceContext::class)->runForConference($event->conferenceId, function () use ($event) {
            $registration = Registration::query()->with(['conference', 'profile', 'user'])->find($event->registrationId);
            $decision = Decision::query()->with('submission')->find($event->decisionId);
            if ($registration === null || $decision === null) {
                return;
            }

            $sent = app(MailSender::class)->send($registration, EmailType::Decision, new ConferenceMail(
                __('mail.decision.subject', ['conference' => $registration->conference->name]),
                'decision',
                ['registration' => $registration, 'conference' => $registration->conference, 'decision' => $decision],
            ));

            if ($sent) {
                app(MarkDecisionNotified::class)->handle($decision->id);
            }
        });
    }
}
