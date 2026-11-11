<?php

use App\Modules\Invoicing\Actions\RenderInvoicePdf;
use App\Modules\Invoicing\Actions\RetryInvoiceIssue;
use App\Modules\Invoicing\Enums\InvoiceStatus;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Payments\Actions\RecordPayment;
use App\Shared\Actions\DomainException;
use App\Shared\Auth\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->conference = makeConference(2027);
    $this->cashier = paymentsOrganizer($this->conference);
    ['registration' => $registration] = registerParticipant($this->conference, 'speaker');
    inConference($this->conference);

    $this->mock(RenderInvoicePdf::class)->shouldReceive('handle')->once()->andThrow(new RuntimeException('temporary'));
    app(RecordPayment::class)->handle($this->cashier, $registration, ['amount' => '180', 'paid_on' => today()->toDateString()]);
    $this->app->forgetInstance(RenderInvoicePdf::class);
    $this->failed = Invoice::query()->sole();
});

// R24
it('issues a failed invoice again', function () {
    $invoice = app(RetryInvoiceIssue::class)->handle($this->cashier, $this->failed);

    expect($invoice->id)->toBe($this->failed->id)
        ->and($invoice->status)->toBe(InvoiceStatus::Issued)
        ->and($invoice->number)->toBe('TEST-ANS2027-0001');
});

it('requires the delegated permission', function () {
    app(RetryInvoiceIssue::class)->handle(staff($this->conference, Role::Organizer), $this->failed);
})->throws(AuthorizationException::class);

it('refuses an issued invoice', function () {
    $issued = app(RetryInvoiceIssue::class)->handle($this->cashier, $this->failed);

    app(RetryInvoiceIssue::class)->handle($this->cashier, $issued);
})->throws(DomainException::class);
