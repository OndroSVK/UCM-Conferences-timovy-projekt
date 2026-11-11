<?php

namespace App\Modules\Payments\Models;

use App\Modules\Auth\Models\User;
use App\Modules\Registration\Models\Registration;
use App\Shared\Conference\BelongsToConference;
use App\Shared\Money\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $registration_id
 * @property int $amount_cents
 * @property string $currency
 * @property \Illuminate\Support\Carbon $paid_on
 * @property string $variable_symbol
 * @property int $recorded_by
 */
class Payment extends Model
{
    use BelongsToConference;

    protected $fillable = [
        'conference_id', 'registration_id', 'amount_cents', 'currency',
        'paid_on', 'variable_symbol', 'recorded_by', 'note',
    ];

    protected function casts(): array
    {
        return ['amount_cents' => 'integer', 'paid_on' => 'date'];
    }

    public function amount(): Money
    {
        return Money::of($this->amount_cents, $this->currency);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
