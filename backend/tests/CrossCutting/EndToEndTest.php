<?php

use App\Modules\Auth\Actions\AuthenticateUser;
use App\Modules\Auth\Enums\LoginDestination;
use App\Modules\Invoicing\Actions\DownloadInvoice;
use App\Modules\Notifications\Models\EmailLog;
use App\Modules\ParticipantPortal\Actions\GetMyRegistrationDetail;
use App\Modules\ParticipantPortal\Actions\ListMyConferences;
use App\Modules\Payments\Actions\FindRegistrationByVariableSymbol;
use App\Modules\Payments\Actions\RecordPayment;
use App\Modules\Submissions\Actions\RecordCommitteeDecision;
use App\Modules\Submissions\Actions\SubmitSubmission;
use App\Modules\Workflow\Enums\PaymentStatus;
use App\Modules\Workflow\Enums\RegistrationStatus;
use App\Modules\Workflow\Enums\SubmissionStatus;
use App\Shared\Auth\Role;
use Illuminate\Support\Facades\Storage;

// backend.md 11.8 — registration → payment → invoice → submission → decision → confirmation.
it('runs the whole prototype path on the level of Actions', function () {
    Storage::fake('local');
    $ans27 = makeConference(2027);
    makeConference(2028); // a second conference running at the same time
    $cashier = paymentsOrganizer($ans27);
    $committee = staff($ans27, Role::ProgrammeCommittee);

    // 1. registration (US-03)
    ['user' => $author, 'registration' => $registration] = registerParticipant($ans27, 'speaker', ['email' => 'e2e@example.test']);

    // 2. login (US-02)
    $login = app(AuthenticateUser::class)->handle(['email' => 'e2e@example.test', 'password' => 'secret-password'], '192.0.2.10');
    expect($login['destination'])->toBe(LoginDestination::ParticipantPortal);

    // 3. user page with instructions (US-08)
    $mine = app(ListMyConferences::class)->handle($author);
    $detail = app(GetMyRegistrationDetail::class)->handle($author, $mine->first()->id);
    $vs = $detail['payment_instructions']['variable_symbol'];

    // 4. organizer finds the VS and records a partial and the rest (US-12)
    inConference($ans27);
    $found = app(FindRegistrationByVariableSymbol::class)->handle($cashier, $vs);
    app(RecordPayment::class)->handle($cashier, $found, ['amount' => '80', 'paid_on' => today()->toDateString()]);
    expect(freshRegistration($registration)->payment_status)->toBe(PaymentStatus::PartiallyPaid);
    app(RecordPayment::class)->handle($cashier, freshRegistration($registration), ['amount' => '100', 'paid_on' => today()->toDateString()]);

    // 5. invoice visible to the participant
    $detail = app(GetMyRegistrationDetail::class)->handle($author, $registration->id);
    expect($detail['statuses']['payment'])->toBe(PaymentStatus::Paid)
        ->and($detail['invoices'])->toHaveCount(1)
        ->and($detail['payment_instructions'])->toBeNull();
    $invoiceFile = app(DownloadInvoice::class)->handle($author, $detail['invoices']->first());
    Storage::disk('local')->assertExists($invoiceFile->path);

    // 6. submission and committee decision
    inConference($ans27);
    $submission = $registration->submissions()->first();
    app(SubmitSubmission::class)->handle($author, $submission, ['title' => 'End-to-end paper', 'file' => fakePdf()]);
    app(RecordCommitteeDecision::class)->handle($committee, $submission->fresh(), ['decision' => 'accepted', 'justification' => 'Accepted.']);

    // 7. participation confirmed automatically
    $detail = app(GetMyRegistrationDetail::class)->handle($author, $registration->id);
    expect($detail['statuses'])->toBe([
        'registration' => RegistrationStatus::Confirmed,
        'payment' => PaymentStatus::Paid,
        'submission' => SubmissionStatus::Accepted,
    ]);

    // 8. every step sent its e-mail
    inConference($ans27);
    expect(EmailLog::query()->where('result', EmailLog::SENT)->pluck('type')->map->value->sort()->values()->all())
        ->toBe(['decision', 'participation_confirmed', 'payment_received', 'registration_confirmation', 'submission_received']);
});
