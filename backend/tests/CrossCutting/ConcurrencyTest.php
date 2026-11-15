<?php

use App\Modules\Invoicing\Actions\IssueInvoice;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Payments\Actions\RecordPayment;
use App\Modules\Registration\Actions\GenerateVariableSymbol;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

// backend.md 11.5 and 11.6 — VS and invoices under concurrency.
//
// These tests show the last safety net (unique indexes) on any database. Real lock waits
// cannot be shown inside the test transaction; they are checked on MariaDB by
// `php artisan conferences:db-check` (second connection holds FOR UPDATE, the first must
// wait), see docs/prepojenie-databazy.md.

it('the unique index stops a duplicate invoice even if the check were bypassed', function () {
    Storage::fake('local');
    $conference = makeConference(2027);
    ['registration' => $registration] = registerParticipant($conference);
    inConference($conference);
    app(RecordPayment::class)->handle(paymentsOrganizer($conference), $registration, ['amount' => '180', 'paid_on' => today()->toDateString()]);

    $invoice = Invoice::query()->sole();

    expect(fn () => Invoice::query()->create(array_merge(
        $invoice->only(['conference_id', 'registration_id', 'type', 'amount_cents', 'currency', 'status']),
        ['number' => 'DUPLICATE'],
    )))->toThrow(QueryException::class);

    expect(app(IssueInvoice::class)->handle($registration->id)->id)->toBe($invoice->id);
});

it('the unique index stops a duplicate variable symbol', function () {
    $conference = makeConference(2027);
    ['registration' => $first] = registerParticipant($conference);
    DB::table('variable_symbol_sequences')->update(['last_value' => 0]); // a broken counter

    expect(fn () => registerParticipant($conference))->toThrow(QueryException::class);
    expect(app(GenerateVariableSymbol::class)->handle($conference))->toBe($first->variable_symbol); // would repeat without the index
});
