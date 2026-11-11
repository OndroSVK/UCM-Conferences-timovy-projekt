<?php

namespace App\Modules\Registration\Models;

use App\Modules\Auth\Models\User;
use App\Modules\Conferences\Enums\ParticipationCategoryCode;
use App\Modules\Conferences\Models\ParticipationCategory;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Payments\Models\Payment;
use App\Modules\Registration\Enums\BillingType;
use App\Modules\Submissions\Models\Submission;
use App\Modules\Workflow\Contracts\HasWorkflowStatus;
use App\Modules\Workflow\Enums\PaymentStatus;
use App\Modules\Workflow\Enums\RegistrationStatus;
use App\Shared\Conference\BelongsToConference;
use App\Shared\Money\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Participation of one account in one conference.
 * status and payment_status are not fillable: only ChangeStatus writes them.
 *
 * @property int $id
 * @property int $conference_id
 * @property int $user_id
 * @property ParticipationCategoryCode $category_code
 * @property int $price_cents
 * @property string $currency
 * @property bool $fee_waived
 * @property string $variable_symbol
 * @property RegistrationStatus|null $status
 * @property PaymentStatus|null $payment_status
 */
class Registration extends Model implements HasWorkflowStatus
{
    use BelongsToConference;

    /** Same defaults as the database, so a new instance is complete without a refresh. */
    protected $attributes = ['fee_waived' => false];

    protected $fillable = [
        'conference_id', 'user_id', 'participation_category_id', 'category_code', 'industry',
        'billing_type', 'billing_name', 'billing_street', 'billing_city', 'billing_postal_code',
        'billing_country', 'billing_company_id', 'billing_tax_id', 'billing_vat_id',
        'price_cents', 'currency', 'variable_symbol', 'data_processing_consent_at',
    ];

    protected function casts(): array
    {
        return [
            'category_code' => ParticipationCategoryCode::class,
            'billing_type' => BillingType::class,
            'price_cents' => 'integer',
            'fee_waived' => 'boolean',
            'status' => RegistrationStatus::class,
            'payment_status' => PaymentStatus::class,
            'data_processing_consent_at' => 'datetime',
        ];
    }

    public function workflowRegistrationId(): int
    {
        return $this->id;
    }

    public function workflowConferenceId(): int
    {
        return $this->conference_id;
    }

    public function price(): Money
    {
        return Money::of($this->price_cents, $this->currency);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(ParticipantProfile::class, 'user_id', 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ParticipationCategory::class, 'participation_category_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
