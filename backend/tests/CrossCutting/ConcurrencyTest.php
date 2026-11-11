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
// SQLite (default test DB) serializes all writes, so real lock waits can only be shown on
// MariaDB. The MariaDB tests open a SECOND connection that holds the row lock and check
// that the feature waits for it instead of reading a stale value.
// Run: DB_CONNECTION=mariadb DB_DATABASE=ucm_testing ... vendor/bin/pest --filter=Concurrency

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

describe('MariaDB row locks', function () {
    beforeEach(function () {
        if (DB::getDriverName() !== 'mariadb' && DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Requires MariaDB (see the comment at the top of the file).');
        }

        config(['database.connections.second' => config('database.connections.'.DB::getDefaultConnection())]);
    });

    it('GenerateVariableSymbol waits for a lock held by another transaction', function () {
        $conference = makeConference(2027);
        app(GenerateVariableSymbol::class)->handle($conference); // creates the counter row
        DB::commit(); // leave the RefreshDatabase transaction so the second connection sees the row
        DB::beginTransaction();

        $second = DB::connection('second');
        $second->beginTransaction();
        $second->table('variable_symbol_sequences')->where('prefix', '27')->lockForUpdate()->first();
        DB::statement('SET SESSION innodb_lock_wait_timeout = 1');

        try {
            expect(fn () => app(GenerateVariableSymbol::class)->handle($conference))
                ->toThrow(QueryException::class); // lock wait timeout = the counter is protected
        } finally {
            $second->rollBack();
        }
    });
});
