<?php

namespace App\Modules\Conferences\Models;

use Database\Factories\ConferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Conference (one edition, e.g. ANS 2027). Not conference-scoped itself: it is the scope.
 *
 * @property int $id
 * @property string $name
 * @property string $subdomain
 * @property int $year
 * @property string $currency
 * @property \Illuminate\Support\Carbon $starts_on
 * @property \Illuminate\Support\Carbon $ends_on
 * @property \Illuminate\Support\Carbon $registration_deadline
 * @property \Illuminate\Support\Carbon $payment_deadline
 * @property \Illuminate\Support\Carbon $submission_deadline
 */
class Conference extends Model
{
    /** @use HasFactory<ConferenceFactory> */
    use HasFactory;

    protected $fillable = [
        'name', 'subdomain', 'year', 'venue', 'starts_on', 'ends_on',
        'registration_deadline', 'payment_deadline', 'submission_deadline',
        'currency', 'iban', 'bic', 'payee_name',
        'issuer_name', 'issuer_address', 'issuer_company_id', 'issuer_tax_id', 'issuer_vat_id',
        'accommodation_url',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'registration_deadline' => 'date',
            'payment_deadline' => 'date',
            'submission_deadline' => 'date',
        ];
    }

    protected static function newFactory(): ConferenceFactory
    {
        return ConferenceFactory::new();
    }

    public function participationCategories(): HasMany
    {
        return $this->hasMany(ParticipationCategory::class)->orderBy('sort');
    }

    public function formFields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ConferenceAssignment::class);
    }

    public function isRegistrationOpen(): bool
    {
        return now()->startOfDay()->lte($this->registration_deadline);
    }

    public function isSubmissionOpen(): bool
    {
        return now()->startOfDay()->lte($this->submission_deadline);
    }
}
