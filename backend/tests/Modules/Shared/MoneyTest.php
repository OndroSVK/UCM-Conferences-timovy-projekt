<?php

use App\Shared\Money\Money;

it('parses amounts without floating point', function (string $input, int $cents) {
    expect(Money::parse($input, 'EUR')->cents)->toBe($cents);
})->with([
    ['180', 18000],
    ['180.5', 18050],
    ['180,05', 18005],
    ['0.10', 10],
    ['1 200.00', 120000],
]);

it('rejects invalid amounts', function (string $input) {
    Money::parse($input, 'EUR');
})->with(['-5', 'abc', '1.234', ''])->throws(InvalidArgumentException::class);

it('refuses to mix currencies', function () {
    Money::of(100, 'EUR')->add(Money::of(100, 'CZK'));
})->throws(InvalidArgumentException::class);

it('formats amounts', function () {
    expect(Money::of(18000, 'EUR')->format())->toBe('180.00 EUR')
        ->and(Money::of(-5, 'EUR')->decimal())->toBe('-0.05');
});
