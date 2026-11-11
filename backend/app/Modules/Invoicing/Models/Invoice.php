<?php

namespace App\Modules\Invoicing\Models;

use App\Modules\Invoicing\Enums\InvoiceStatus;
use App\Modules\Invoicing\Enums\InvoiceType;
use App\Modules\Registration\Models\Registration;
use App\Shared\Conference\BelongsToConference;
use App\Shared\Money\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An issued invoice never changes; a correction is a credit note (full version).
 *
 * @property int $id
 * @property int $registration_id
 * @property InvoiceType $type
 * @property string|null $number
 * @property int $amount_cents
 * @property string $currency
 * @property InvoiceStatus $status
 * @property string|null $pdf_path
 * @property \Illuminate\Support\Carbon|null $issued_at
 * @property string|null $failure_reason
 */
class Invoice extends Model
{
    use BelongsToConference;

    protected $fillable = [
        'conference_id', 'registration_id', 'type', 'number', 'amount_cents', 'currency',
        'status', 'pdf_path', 'issued_at', 'failure_reason',
    ];

    protected $hidden = ['pdf_path'];

    protected function casts(): array
    {
        return [
            'type' => InvoiceType::class,
            'status' => InvoiceStatus::class,
            'amount_cents' => 'integer',
            'issued_at' => 'datetime',
        ];
    }

    public function amount(): Money
    {
        return Money::of($this->amount_cents, $this->currency);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
