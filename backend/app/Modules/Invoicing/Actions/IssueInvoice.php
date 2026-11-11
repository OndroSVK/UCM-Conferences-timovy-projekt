<?php

namespace App\Modules\Invoicing\Actions;

use App\Modules\Invoicing\Enums\InvoiceStatus;
use App\Modules\Invoicing\Enums\InvoiceType;
use App\Modules\Invoicing\Events\InvoiceIssued;
use App\Modules\Invoicing\Events\InvoiceIssueFailed;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Registration\Models\Registration;
use App\Modules\Workflow\Enums\PaymentStatus;
use App\Shared\Actions\Action;
use App\Shared\Actions\DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Test invoice EXACTLY once per registration (unique index + check under a row lock),
 * issuer = conference data, number from the conference series, event InvoiceIssued.
 *
 * On failure a "failed" record stays for RetryInvoiceIssue (R24) and InvoiceIssueFailed
 * is raised; the payment is never touched.
 */
final class IssueInvoice extends Action
{
    public function __construct(private readonly RenderInvoicePdf $render)
    {
    }

    public function handle(int $registrationId): Invoice
    {
        try {
            return DB::transaction(function () use ($registrationId): Invoice {
                // Serializes concurrent issuing for one registration (read lock only).
                $registration = Registration::query()->lockForUpdate()->findOrFail($registrationId);

                $existing = Invoice::query()
                    ->where('registration_id', $registration->id)
                    ->where('type', InvoiceType::Invoice->value)
                    ->lockForUpdate()
                    ->first();

                if ($existing?->status === InvoiceStatus::Issued) {
                    return $existing;
                }

                if ($registration->payment_status !== PaymentStatus::Paid) {
                    throw new DomainException(__('invoices.not_paid'));
                }

                $conference = $registration->conference()->firstOrFail();

                $invoice = $existing ?? new Invoice([
                    'conference_id' => $registration->conference_id,
                    'registration_id' => $registration->id,
                    'type' => InvoiceType::Invoice,
                ]);

                $invoice->fill([
                    'number' => $invoice->number ?? $this->nextNumber($registration->conference_id, $conference->subdomain),
                    'amount_cents' => $registration->price_cents,
                    'currency' => $registration->currency,
                    'status' => InvoiceStatus::Issued,
                    'issued_at' => now(),
                    'failure_reason' => null,
                ]);

                $pdf = $this->render->handle($invoice, $registration, $conference);
                $path = sprintf('invoices/%d/%s.pdf', $registration->conference_id, $invoice->number);
                Storage::disk((string) config('conferences.invoices.disk'))->put($path, $pdf);

                $invoice->pdf_path = $path;
                $invoice->save();

                InvoiceIssued::dispatch($invoice->id, $registration->id, $registration->conference_id);

                return $invoice;
            });
        } catch (DomainException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return $this->recordFailure($registrationId, $e);
        }
    }

    private function nextNumber(int $conferenceId, string $subdomain): string
    {
        DB::table('invoice_number_sequences')->insertOrIgnore([
            'conference_id' => $conferenceId,
            'last_value' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $next = 1 + (int) DB::table('invoice_number_sequences')
            ->where('conference_id', $conferenceId)
            ->lockForUpdate()
            ->value('last_value');

        DB::table('invoice_number_sequences')
            ->where('conference_id', $conferenceId)
            ->update(['last_value' => $next, 'updated_at' => now()]);

        // PROPOSAL: O29 — format not verified with the faculty, e.g. TEST-ANS2027-0001.
        return sprintf('TEST-%s-%04d', strtoupper($subdomain), $next);
    }

    private function recordFailure(int $registrationId, Throwable $e): Invoice
    {
        return DB::transaction(function () use ($registrationId, $e): Invoice {
            $registration = Registration::query()->findOrFail($registrationId);

            $invoice = Invoice::query()->firstOrNew([
                'registration_id' => $registration->id,
                'type' => InvoiceType::Invoice->value,
            ]);

            if ($invoice->status === InvoiceStatus::Issued) {
                return $invoice;
            }

            $invoice->fill([
                'conference_id' => $registration->conference_id,
                'amount_cents' => $registration->price_cents,
                'currency' => $registration->currency,
                'status' => InvoiceStatus::Failed,
                'failure_reason' => mb_substr($e->getMessage(), 0, 1000),
            ])->save();

            InvoiceIssueFailed::dispatch($invoice->id, $registration->id, $registration->conference_id);

            return $invoice;
        });
    }
}
