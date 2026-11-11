<?php

namespace App\Modules\Registration\Actions;

use App\Modules\Conferences\Models\Conference;
use App\Shared\Actions\Action;
use App\Shared\Actions\DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Unique variable symbol (backend.md 10). The only place that knows its format.
 *
 * Format (BR5, temporary — BO1): RR + NNNNNN, RR = last two digits of the conference year.
 * PROPOSAL: BO1 — the sequence is shared by all conferences with the same RR; a counter per
 * conference would give two conferences of one year identical symbols.
 *
 * The counter row is locked FOR UPDATE, so concurrent registrations never get the same VS;
 * the unique index on registrations.variable_symbol is the last safety net.
 */
final class GenerateVariableSymbol extends Action
{
    private const MAX_SEQUENCE = 999_999;

    public function handle(Conference $conference): string
    {
        $prefix = str_pad((string) ($conference->year % 100), 2, '0', STR_PAD_LEFT);

        return DB::transaction(function () use ($prefix): string {
            DB::table('variable_symbol_sequences')->insertOrIgnore([
                'prefix' => $prefix,
                'last_value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $current = (int) DB::table('variable_symbol_sequences')
                ->where('prefix', $prefix)
                ->lockForUpdate()
                ->value('last_value');

            $next = $current + 1;

            if ($next > self::MAX_SEQUENCE) {
                throw new DomainException(__('errors.variable_symbol_exhausted'));
            }

            DB::table('variable_symbol_sequences')
                ->where('prefix', $prefix)
                ->update(['last_value' => $next, 'updated_at' => now()]);

            return $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
        });
    }
}
