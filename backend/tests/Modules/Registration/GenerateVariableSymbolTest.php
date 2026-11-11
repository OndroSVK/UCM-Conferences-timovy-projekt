<?php

use App\Modules\Registration\Actions\GenerateVariableSymbol;
use Illuminate\Support\Facades\DB;

it('generates RR + 6 digits, sequentially and unique', function () {
    $conference = makeConference(2027);
    $generate = app(GenerateVariableSymbol::class);

    $symbols = collect(range(1, 50))->map(fn () => $generate->handle($conference));

    expect($symbols->first())->toBe('27000001')
        ->and($symbols->last())->toBe('27000050')
        ->and($symbols->unique())->toHaveCount(50)
        ->and($symbols->every(fn ($vs) => preg_match('/^\d{8}$/', $vs) === 1))->toBeTrue();
});

// PROPOSAL BO1: two conferences of the same year share the sequence
it('never repeats a VS for two conferences of the same year', function () {
    $a = makeConference(2027);
    $b = makeConference(2027, ['subdomain' => 'other2027']);
    $generate = app(GenerateVariableSymbol::class);

    expect($generate->handle($a))->toBe('27000001')
        ->and($generate->handle($b))->toBe('27000002');
});

it('is protected by a unique index on registrations', function () {
    $conference = makeConference(2027);
    registerParticipant($conference);
    DB::table('variable_symbol_sequences')->update(['last_value' => 0]); // simulate a broken counter

    expect(fn () => registerParticipant($conference))->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});
