<?php

namespace App\Modules\Notifications\Listeners;

use App\Modules\Invoicing\Events\InvoiceIssued;
use App\Modules\Invoicing\Events\InvoiceIssueFailed;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Notifications\Enums\EmailType;
use App\Modules\Notifications\Support\ConferenceMail;
use App\Modules\Notifications\Support\MailSender;
use App\Modules\Payments\Actions\GetPaymentBalance;
use App\Modules\Payments\Events\PaymentSettled;
use App\Modules\Registration\Models\Registration;
use App\Shared\Conference\ConferenceContext;
use App\Shared\Modules\Modules;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Attachment;

/**
 * Payment confirmation after full payment (US-12):
 *  - InvoiceIssued       → with the test invoice attached,
 *  - InvoiceIssueFailed  → without attachment (PROPOSAL: the participant still learns the payment arrived),
 *  - PaymentSettled      → only when Invoicing is switched off (O32 fallback).
 */
final class SendPaymentReceived implements ShouldQueue
{
    public bool $afterCommit = true;

    public function handle(InvoiceIssued|InvoiceIssueFailed|PaymentSettled $event): void
    {
        if ($event instanceof PaymentSettled && Modules::enabled('Invoicing')) {
            return; // the invoice events will follow
        }

        app(ConferenceContext::class)->runForConference($event->conferenceId, function () use ($event) {
            $registration = Registration::query()->with(['conference', 'profile', 'user'])->find($event->registrationId);
            if ($registration === null) {
                return;
            }

            $files = [];
            if ($event instanceof InvoiceIssued) {
                $invoice = Invoice::query()->find($event->invoiceId);
                if ($invoice?->pdf_path) {
                    $files[] = Attachment::fromStorageDisk((string) config('conferences.invoices.disk'), $invoice->pdf_path)
                        ->as($invoice->number.'.pdf')
                        ->withMime('application/pdf');
                }
            }

            app(MailSender::class)->send($registration, EmailType::PaymentReceived, new ConferenceMail(
                __('mail.payment_received.subject', ['conference' => $registration->conference->name]),
                'payment-received',
                [
                    'registration' => $registration,
                    'conference' => $registration->conference,
                    'balance' => app(GetPaymentBalance::class)->handle($registration),
                    'hasInvoice' => $files !== [],
                ],
                $files,
            ));
        });
    }
}
