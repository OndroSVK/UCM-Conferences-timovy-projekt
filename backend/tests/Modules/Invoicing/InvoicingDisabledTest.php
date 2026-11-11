<?php

use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Notifications\Enums\EmailType;
use App\Modules\Notifications\Models\EmailLog;
use App\Modules\ParticipantPortal\Actions\GetMyRegistrationDetail;
use App\Modules\Payments\Actions\RecordPayment;
use App\Modules\Workflow\Enums\PaymentStatus;

// backend.md 3.3 rule 4, O32: switching Invoicing off must not break payments.
it('payments, e-mails and the user page work with Invoicing switched off', function () {
    config(['modules.enabled.Invoicing' => false]);
    $conference = makeConference(2027);
    ['user' => $owner, 'registration' => $registration] = registerParticipant($conference, 'passive_participant');
    inConference($conference);

    app(RecordPayment::class)->handle(paymentsOrganizer($conference), $registration, ['amount' => '90', 'paid_on' => today()->toDateString()]);

    expect(freshRegistration($registration)->payment_status)->toBe(PaymentStatus::Paid)
        ->and(Invoice::query()->count())->toBe(0)
        ->and(EmailLog::query()->where('type', EmailType::PaymentReceived->value)->count())->toBe(1);

    $detail = app(GetMyRegistrationDetail::class)->handle($owner, $registration->id);
    expect($detail['invoices'])->toBeEmpty();
});
