<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * One command that tells whether the database is connected the way the backend needs
 * (docs/prepojenie-databazy.md). Read-only except for one temporary counter row used
 * by the lock check, which is always removed.
 *
 * Exit code 0 = every check passed; 1 = at least one failed.
 */
final class CheckDatabaseCommand extends Command
{
    private const EXPECTED_TABLES = [
        'users', 'user_roles', 'conferences', 'participation_categories', 'form_fields', 'conference_user',
        'participant_profiles', 'registrations', 'variable_symbol_sequences',
        'submissions', 'submission_versions', 'decisions', 'reviews',
        'status_history', 'payments', 'invoices', 'invoice_number_sequences', 'email_log',
        'sessions', 'cache', 'jobs', 'password_reset_tokens',
    ];

    private const LOCK_PROBE_PREFIX = '_chk';

    protected $signature = 'conferences:db-check
        {--allow-sqlite : Do not fail when the driver is not MariaDB (local development only)}
        {--skip-locks : Skip the row lock check (needs a second connection)}';

    protected $description = 'Check the database connection, schema and row locks required by the backend';

    /** @var list<array{string, string, string}> */
    private array $rows = [];

    private bool $failed = false;

    public function handle(): int
    {
        $this->rows = [];
        $this->failed = false;

        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $isMaria = in_array($driver, ['mariadb', 'mysql'], true);

        try {
            $connection->getPdo();
            $this->passRow('connection', sprintf('%s @ %s / %s', $driver, config("database.connections.{$connection->getName()}.host") ?? 'file', $connection->getDatabaseName()));
        } catch (Throwable $e) {
            $this->failRow('connection', $e->getMessage());
            $this->printReport();

            return self::FAILURE;
        }

        if ($isMaria) {
            $this->checkMariaDbServer();
        } elseif ($this->option('allow-sqlite')) {
            $this->warnRow('driver', "{$driver} — fine for local development, the target is MariaDB (R35)");
        } else {
            $this->failRow('driver', "{$driver} — expected mariadb. Set DB_CONNECTION=mariadb in .env (or pass --allow-sqlite locally)");
        }

        $this->checkMigrations();
        $this->checkTables($isMaria);
        $this->checkConstraints();
        $this->checkWrite();

        if ($isMaria && ! $this->option('skip-locks')) {
            $this->checkRowLocks();
        } else {
            $this->warnRow('row locks', 'skipped — only meaningful on MariaDB');
        }

        $this->printReport();

        return $this->failed ? self::FAILURE : self::SUCCESS;
    }

    private function checkMariaDbServer(): void
    {
        $version = (string) DB::scalar('select version()');
        stripos($version, 'mariadb') !== false
            ? $this->passRow('server', $version)
            : $this->failRow('server', "{$version} — this is not MariaDB (R35)");

        $database = DB::selectOne('select default_character_set_name as charset, default_collation_name as collation from information_schema.schemata where schema_name = database()');
        ($database->charset ?? '') === 'utf8mb4'
            ? $this->passRow('charset', "{$database->charset} / {$database->collation}")
            : $this->failRow('charset', ($database->charset ?? '?').' — create the database with CHARACTER SET utf8mb4');

        $mode = (string) DB::scalar('select @@session.sql_mode');
        str_contains($mode, 'STRICT_TRANS_TABLES') || str_contains($mode, 'STRICT_ALL_TABLES')
            ? $this->passRow('strict mode', 'on')
            : $this->failRow('strict mode', 'off — keep "strict" => true in config/database.php');

        // Without this an old server adds ON UPDATE CURRENT_TIMESTAMP to the first timestamp
        // column of a table, which would silently rewrite e.g. decisions.decided_at.
        (int) DB::scalar('select @@explicit_defaults_for_timestamp') === 1
            ? $this->passRow('timestamps', 'explicit_defaults_for_timestamp = ON')
            : $this->failRow('timestamps', 'explicit_defaults_for_timestamp is OFF — use MariaDB 10.10+ or switch it ON before migrating');
    }

    private function checkMigrations(): void
    {
        if (! Schema::hasTable('migrations')) {
            $this->failRow('migrations', 'not run yet — php artisan migrate');

            return;
        }

        $migrator = app('migrator');
        $files = array_keys($migrator->getMigrationFiles(array_merge($migrator->paths(), [database_path('migrations')])));
        $pending = array_diff($files, $migrator->getRepository()->getRan());

        $pending === []
            ? $this->passRow('migrations', count($files).' run, none pending')
            : $this->failRow('migrations', count($pending).' pending: '.implode(', ', array_slice(array_values($pending), 0, 3)).' — php artisan migrate');
    }

    private function checkTables(bool $isMaria): void
    {
        $missing = array_values(array_filter(self::EXPECTED_TABLES, fn (string $table) => ! Schema::hasTable($table)));

        $missing === []
            ? $this->passRow('tables', count(self::EXPECTED_TABLES).' expected tables exist')
            : $this->failRow('tables', 'missing: '.implode(', ', $missing));

        if (! $isMaria) {
            return;
        }

        $notInnoDb = DB::table('information_schema.tables')
            ->whereRaw('table_schema = database()')
            ->where('table_type', 'BASE TABLE')
            ->where('engine', '!=', 'InnoDB')
            ->pluck('table_name')
            ->all();

        $notInnoDb === []
            ? $this->passRow('engine', 'all tables are InnoDB (transactions, row locks, foreign keys)')
            : $this->failRow('engine', 'not InnoDB: '.implode(', ', $notInnoDb));
    }

    private function checkConstraints(): void
    {
        if (! Schema::hasTable('registrations') || ! Schema::hasTable('invoices')) {
            return; // already reported by the tables check
        }

        $uniques = [
            'registrations' => [['variable_symbol'], ['conference_id', 'user_id']],
            'invoices' => [['registration_id', 'type']],
            'submission_versions' => [['submission_id', 'version']],
            'users' => [['email']],
        ];

        $missing = [];
        foreach ($uniques as $table => $sets) {
            $existing = collect(Schema::getIndexes($table))->where('unique', true)->pluck('columns')->all();
            foreach ($sets as $columns) {
                if (! in_array($columns, $existing, false)) {
                    $missing[] = $table.'('.implode(',', $columns).')';
                }
            }
        }

        $missing === []
            ? $this->passRow('unique indexes', 'VS, one registration per conference, one invoice per registration, versions, e-mail')
            : $this->failRow('unique indexes', 'missing: '.implode('; ', $missing));

        $foreignKeys = count(Schema::getForeignKeys('registrations')) + count(Schema::getForeignKeys('payments'));
        $foreignKeys >= 5
            ? $this->passRow('foreign keys', 'present on registrations and payments')
            : $this->failRow('foreign keys', 'missing — were the tables created by the migrations?');
    }

    private function checkWrite(): void
    {
        if (! Schema::hasTable('variable_symbol_sequences')) {
            return;
        }

        try {
            DB::beginTransaction();
            DB::table('variable_symbol_sequences')->insert(['prefix' => '_w', 'last_value' => 1, 'created_at' => now(), 'updated_at' => now()]);
            $read = (int) DB::table('variable_symbol_sequences')->where('prefix', '_w')->value('last_value');
            DB::rollBack();

            $stillThere = DB::table('variable_symbol_sequences')->where('prefix', '_w')->exists();

            $read === 1 && ! $stillThere
                ? $this->passRow('write + rollback', 'insert, read and rollback work')
                : $this->failRow('write + rollback', 'the rollback did not remove the test row — the table is not transactional');
        } catch (Throwable $e) {
            DB::rollBack();
            $this->failRow('write + rollback', $e->getMessage());
        }
    }

    /**
     * A second connection holds FOR UPDATE on a counter row; the first one must wait
     * (and time out), not read the row. This is what protects variable symbols and
     * invoice numbers under concurrency (backend.md 10, 11.5, 11.6).
     */
    private function checkRowLocks(): void
    {
        $table = 'variable_symbol_sequences';
        $default = DB::getDefaultConnection();
        config(['database.connections.db_check_second' => config("database.connections.{$default}")]);
        $second = DB::connection('db_check_second');

        try {
            DB::table($table)->insertOrIgnore(['prefix' => self::LOCK_PROBE_PREFIX, 'last_value' => 0, 'created_at' => now(), 'updated_at' => now()]);

            $second->beginTransaction();
            $second->table($table)->where('prefix', self::LOCK_PROBE_PREFIX)->lockForUpdate()->first();

            DB::statement('SET SESSION innodb_lock_wait_timeout = 1');

            try {
                DB::transaction(fn () => DB::table($table)->where('prefix', self::LOCK_PROBE_PREFIX)->lockForUpdate()->first());
                $this->failRow('row locks', 'a locked row was read without waiting — FOR UPDATE does not block');
            } catch (QueryException $e) {
                str_contains($e->getMessage(), '1205') || stripos($e->getMessage(), 'lock wait timeout') !== false
                    ? $this->passRow('row locks', 'a second transaction waits for FOR UPDATE (lock wait timeout as expected)')
                    : $this->failRow('row locks', 'unexpected error: '.$e->getMessage());
            }
        } catch (Throwable $e) {
            $this->failRow('row locks', $e->getMessage());
        } finally {
            rescue(fn () => $second->rollBack(), report: false);
            rescue(fn () => DB::statement('SET SESSION innodb_lock_wait_timeout = DEFAULT'), report: false);
            rescue(fn () => DB::table($table)->where('prefix', self::LOCK_PROBE_PREFIX)->delete(), report: false);
            DB::purge('db_check_second');
        }
    }

    private function passRow(string $check, string $detail): void
    {
        $this->rows[] = ['OK', $check, $detail];
    }

    private function warnRow(string $check, string $detail): void
    {
        $this->rows[] = ['WARN', $check, $detail];
    }

    private function failRow(string $check, string $detail): void
    {
        $this->failed = true;
        $this->rows[] = ['FAIL', $check, $detail];
    }

    private function printReport(): void
    {
        $this->table(['', 'Check', 'Result'], $this->rows);
        $this->failed
            ? $this->error('Database check FAILED. See docs/prepojenie-databazy.md, section "Riešenie problémov".')
            : $this->info('Database check passed.');
    }
}
