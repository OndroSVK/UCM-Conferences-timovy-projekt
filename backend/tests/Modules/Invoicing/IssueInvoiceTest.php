<?php

use App\Modules\Invoicing\Actions\IssueInvoice;
use App\Modules\Invoicing\Enums\InvoiceStatus;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Payments\Actions\RecordPayment;
use App\Shared\Actions\DomainException;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->conference = makeConference(2027);
    $this->cashier = paymentsOrganizer($this->conference);
    ['registration' => $this->registration] = registerParticipant($this->conference, 'speaker');
    inConference($this->conference);
});

// US-12
it('issues the test invoice automatically when the payment is settled', function () {
    app(RecordPayment::class)->handle($this->cashier, $this->registration, ['amount' => '180', 'paid_on' => today()->toDateString()]);

    $invoice = Invoice::query()->sole();
    expect($invoice->status)->toBe(InvoiceStatus::Issued)
        ->and($invoice->number)->toBe('TEST-ANS2027-0001')
        ->and($invoice->amount_cents)->toBe(18000);
    Storage::disk('local')->assertExists($invoice->pdf_path);
});

it('issues exactly once, also when called repeatedly', function () {
    app(RecordPayment::class)->handle($this->cashier, $this->registration, ['amount' => '180', 'paid_on' => today()->toDateString()]);

    $first = Invoice::query()->sole();
    $again = app(IssueInvoice::class)->handle($this->registration->id);
    app(IssueInvoice::class)->handle($this->registration->id);

    expect($again->id)->toBe($first->id)
        ->and($again->number)->toBe($first->number)
        ->and(Invoice::query()->count())->toBe(1);
});

it('numbers invoices per conference', function () {
    ['registration' => $second] = registerParticipant($this->conference, 'poster_author');
    inConference($this->conference);

    app(RecordPayment::class)->handle($this->cashier, $this->registration, ['amount' => '180', 'paid_on' => today()->toDateString()]);
    app(RecordPayment::class)->handle($this->cashier, $second, ['amount' => '120', 'paid_on' => today()->toDateString()]);

    expect(Invoice::query()->orderBy('id')->pluck('number')->all())->toBe(['TEST-ANS2027-0001', 'TEST-ANS2027-0002']);
});

it('refuses an unpaid registration', function () {
    app(IssueInvoice::class)->handle($this->registration->id);
})->throws(DomainException::class);

// R24: failure does not undo the payment
it('records a failed invoice and keeps the payment when PDF rendering fails', function () {
    $this->mock(\App\Modules\Invoicing\Actions\RenderInvoicePdf::class)
        ->shouldReceive('handle')->andThrow(new RuntimeException('dompdf crashed'));

    app(RecordPayment::class)->handle($this->cashier, $this->registration, ['amount' => '180', 'paid_on' => today()->toDateString()]);

    $invoice = Invoice::query()->sole();
    expect($invoice->status)->toBe(InvoiceStatus::Failed)
        ->and($invoice->failure_reason)->toContain('dompdf crashed')
        ->and($invoice->number)->toBeNull()
        ->and(freshRegistration($this->registration)->payment_status->value)->toBe('paid');
});
