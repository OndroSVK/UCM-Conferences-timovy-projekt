<?php

namespace Database\Factories;

use App\Modules\Conferences\Models\Conference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fictional conference data; the IBAN is a well-known test number, not a real account.
 *
 * @extends Factory<Conference>
 */
class ConferenceFactory extends Factory
{
    protected $model = Conference::class;

    public function definition(): array
    {
        $year = (int) now()->addYear()->format('Y');

        return [
            'name' => "Applied Natural Sciences {$year}",
            'subdomain' => 'ans'.$year.fake()->unique()->numerify('##'),
            'year' => $year,
            'venue' => 'Example Venue, Trnava',
            'starts_on' => now()->addMonths(10)->toDateString(),
            'ends_on' => now()->addMonths(10)->addDays(2)->toDateString(),
            'registration_deadline' => now()->addMonths(8)->toDateString(),
            'payment_deadline' => now()->addMonths(9)->toDateString(),
            'submission_deadline' => now()->addMonths(7)->toDateString(),
            'currency' => 'EUR',
            'iban' => 'SK3112000000198742637541',
            'bic' => 'TESTSKBX',
            'payee_name' => 'Example Faculty (test)',
            'issuer_name' => 'Example Faculty (test)',
            'issuer_address' => 'Example Street 1, 917 01 Trnava, Slovakia',
            'issuer_company_id' => '00000000',
            'issuer_tax_id' => '0000000000',
            'issuer_vat_id' => null,
            'accommodation_url' => 'https://example.com/accommodation',
        ];
    }

    public function edition(int $year): static
    {
        return $this->state(fn () => [
            'name' => "Applied Natural Sciences {$year}",
            'subdomain' => "ans{$year}",
            'year' => $year,
        ]);
    }
}
