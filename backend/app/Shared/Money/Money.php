<?php

namespace App\Shared\Money;

use InvalidArgumentException;

/**
 * Amount in minor units (cents) + ISO 4217 currency. No floats (backend.md 4.4).
 */
final readonly class Money
{
    public function __construct(
        public int $cents,
        public string $currency,
    ) {
        if (! preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new InvalidArgumentException("Invalid currency [{$currency}].");
        }
    }

    public static function of(int $cents, string $currency): self
    {
        return new self($cents, $currency);
    }

    public static function zero(string $currency): self
    {
        return new self(0, $currency);
    }

    /**
     * Parses user input such as "120", "120.5", "120,50" without floating point arithmetic.
     */
    public static function parse(string $amount, string $currency): self
    {
        $normalized = str_replace([' ', ','], ['', '.'], trim($amount));

        if (! preg_match('/^(\d{1,9})(?:\.(\d{1,2}))?$/', $normalized, $m)) {
            throw new InvalidArgumentException("Invalid amount [{$amount}].");
        }

        $cents = ((int) $m[1]) * 100 + (int) str_pad($m[2] ?? '0', 2, '0');

        return new self($cents, $currency);
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->cents + $other->cents, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->cents - $other->cents, $this->currency);
    }

    public function isZero(): bool
    {
        return $this->cents === 0;
    }

    public function isPositive(): bool
    {
        return $this->cents > 0;
    }

    public function isNegative(): bool
    {
        return $this->cents < 0;
    }

    public function max(self $other): self
    {
        $this->assertSameCurrency($other);

        return $this->cents >= $other->cents ? $this : $other;
    }

    /** "180.00" — machine format, used in exports and payment instructions. */
    public function decimal(): string
    {
        $sign = $this->cents < 0 ? '-' : '';
        $abs = abs($this->cents);

        return sprintf('%s%d.%02d', $sign, intdiv($abs, 100), $abs % 100);
    }

    /** "180.00 EUR" */
    public function format(): string
    {
        return $this->decimal().' '.$this->currency;
    }

    private function assertSameCurrency(self $other): void
    {
        if ($other->currency !== $this->currency) {
            throw new InvalidArgumentException("Currency mismatch [{$this->currency}] vs [{$other->currency}].");
        }
    }
}
