<?php

namespace App\Modules\ParticipantPortal\Actions;

use App\Modules\Payments\Actions\GetPaymentBalance;
use App\Modules\Registration\Models\Registration;
use App\Modules\Workflow\Enums\RegistrationStatus;
use App\Shared\Actions\Action;
use App\Shared\Money\Money;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Personalised payment instructions (R1): IBAN and payee from the conference, the
 * remaining amount, own VS, due date. Null = no payment request (fee waiver, zero
 * price, fully paid or cancelled registration).
 */
final class GetPaymentInstructions extends Action
{
    public function __construct(private readonly GetPaymentBalance $balance)
    {
    }

    /**
     * @return array{iban:string, bic:?string, payee:string, amount:Money, variable_symbol:string, due_date:\Illuminate\Support\Carbon, message:string}|null
     */
    public function handle(Authenticatable $actor, Registration $registration): ?array
    {
        $this->authorizeOwner($actor, $registration->user_id);

        if ($registration->payment_status === null || $registration->status === RegistrationStatus::Cancelled) {
            return null;
        }

        $balance = $this->balance->handle($registration);
        if (! $balance->requiresPayment()) {
            return null;
        }

        $conference = $registration->conference()->firstOrFail();

        return [
            'iban' => $conference->iban,
            'bic' => $conference->bic,
            'payee' => $conference->payee_name,
            'amount' => $balance->remaining,
            'variable_symbol' => $registration->variable_symbol,
            'due_date' => $conference->payment_deadline,
            'message' => $conference->name,
        ];
    }
}
