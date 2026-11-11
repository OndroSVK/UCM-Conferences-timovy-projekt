<?php

use App\Modules\Invoicing\Actions\RenderInvoicePdf;
use App\Modules\Invoicing\Enums\InvoiceStatus;
use App\Modules\Invoicing\Enums\InvoiceType;
use App\Modules\Invoicing\Models\Invoice;

// R20, R26
it('renders a PDF marked TEST INVOICE', function () {
    $conference = makeConference(2027);
    ['registration' => $registration] = registerParticipant($conference, 'speaker');
    inConference($conference);

    $invoice = new Invoice([
        'conference_id' => $conference->id,
        'registration_id' => $registration->id,
        'type' => InvoiceType::Invoice,
        'number' => 'TEST-ANS2027-0001',
        'amount_cents' => 18000,
        'currency' => 'EUR',
        'status' => InvoiceStatus::Issued,
        'issued_at' => now(),
    ]);

    $pdf = app(RenderInvoicePdf::class)->handle($invoice, $registration, $conference);

    // dompdf writes text with embedded Unicode fonts as UTF-16BE.
    $watermark = mb_convert_encoding('TEST INVOICE', 'UTF-16BE', 'UTF-8');

    expect(substr($pdf, 0, 5))->toBe('%PDF-')
        ->and(substr_count($pdf, $watermark))->toBeGreaterThanOrEqual(2) // banner + diagonal watermark
        ->and($pdf)->toContain('0.850 0.000 0.000 RG'); // red stroke of the cross
});
