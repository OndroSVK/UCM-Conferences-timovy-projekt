<?php

use App\Modules\Invoicing\Actions\RenderInvoicePdf;
use App\Modules\Notifications\Enums\EmailType;
use App\Modules\Notifications\Models\EmailLog;
use App\Modules\Payments\Actions\RecordPayment;
use App\Modules\Submissions\Actions\RecordCommitteeDecision;
use App\Modules\Submissions\Actions\ReplaceSubmissionFile;
use App\Modules\Submissions\Actions\SubmitSubmission;
use App\Modules\Submissions\Models\Decision;
use App\Modules\Notifications\Support\ConferenceMail;
use App\Shared\Auth\Role;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

// Covers the listeners SendRegistrationConfirmation, SendPaymentReceived, SendSubmissionReceived,
// SendDecision, SendParticipationConfirmed and LogEmail.

beforeEach(function () {
    Storage::fake('local');
    $this->conference = makeConference(2027);
    ['user' => $this->author, 'registration' => $this->registration] = registerParticipant($this->conference, 'speaker', ['email' => 'author@example.test']);
    inConference($this->conference);
    $this->logged = fn (EmailType $type) => EmailLog::query()->where('type', $type->value)->get();
});

it('logs the registration confirmation with the recipient and result', function () {
    $log = ($this->logged)(EmailType::RegistrationConfirmation)->sole();

    expect($log->recipient)->toBe('author@example.test')
        ->and($log->registration_id)->toBe($this->registration->id)
        ->and($log->result)->toBe(EmailLog::SENT);
});

it('sends the payment confirmation with the test invoice attached (US-12)', function () {
    Mail::fake();

    app(RecordPayment::class)->handle(paymentsOrganizer($this->conference), $this->registration, ['amount' => '180', 'paid_on' => today()->toDateString()]);

    Mail::assertSent(ConferenceMail::class, fn (ConferenceMail $mail) => $mail->hasTo('author@example.test')
        && count($mail->attachments()) === 1);
});

it('a partial payment sends no payment confirmation', function () {
    app(RecordPayment::class)->handle(paymentsOrganizer($this->conference), $this->registration, ['amount' => '10', 'paid_on' => today()->toDateString()]);

    expect(($this->logged)(EmailType::PaymentReceived))->toBeEmpty();
});

it('sends the payment confirmation without attachment when the invoice failed', function () {
    $this->mock(RenderInvoicePdf::class)->shouldReceive('handle')->andThrow(new RuntimeException('broken'));

    app(RecordPayment::class)->handle(paymentsOrganizer($this->conference), $this->registration, ['amount' => '180', 'paid_on' => today()->toDateString()]);

    expect(($this->logged)(EmailType::PaymentReceived))->toHaveCount(1);
});

it('confirms a submission and a new version', function () {
    $submission = $this->registration->submissions()->first();
    app(SubmitSubmission::class)->handle($this->author, $submission, ['title' => 'Paper', 'file' => fakePdf()]);
    app(ReplaceSubmissionFile::class)->handle($this->author, $submission->fresh(), ['file' => fakePdf()]);

    expect(($this->logged)(EmailType::SubmissionReceived))->toHaveCount(2);
});

it('sends the decision, marks it notified and then confirms participation', function () {
    app(RecordPayment::class)->handle(paymentsOrganizer($this->conference), $this->registration, ['amount' => '180', 'paid_on' => today()->toDateString()]);
    $submission = $this->registration->submissions()->first();
    app(SubmitSubmission::class)->handle($this->author, $submission, ['title' => 'Paper', 'file' => fakePdf()]);

    app(RecordCommitteeDecision::class)->handle(staff($this->conference, Role::ProgrammeCommittee), $submission->fresh(), [
        'decision' => 'accepted', 'justification' => 'Well done.',
    ]);

    expect(($this->logged)(EmailType::Decision))->toHaveCount(1)
        ->and(Decision::query()->sole()->notified_at)->not->toBeNull()
        ->and(($this->logged)(EmailType::ParticipationConfirmed))->toHaveCount(1);
});

// backend.md 4.6
it('a failed e-mail is logged and never undoes the operation', function () {
    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP down'));

    ['registration' => $registration] = app(RecordPayment::class)->handle(
        paymentsOrganizer($this->conference),
        $this->registration,
        ['amount' => '180', 'paid_on' => today()->toDateString()],
    ) + ['registration' => $this->registration];

    $failed = ($this->logged)(EmailType::PaymentReceived)->sole();
    expect($failed->result)->toBe(EmailLog::FAILED)
        ->and($failed->error)->toContain('SMTP down')
        ->and(freshRegistration($registration)->payment_status->value)->toBe('paid');
});
