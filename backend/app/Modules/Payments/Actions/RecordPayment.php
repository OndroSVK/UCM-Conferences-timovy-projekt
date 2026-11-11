<?php

namespace App\Modules\Payments\Actions;

use App\Modules\Payments\Events\PaymentRecorded;
use App\Modules\Payments\Events\PaymentSettled;
use App\Modules\Payments\Models\Payment;
use App\Modules\Payments\Support\PaymentBalance;
use App\Modules\Registration\Models\Registration;
use App\Modules\Workflow\Actions\ChangeStatus;
use App\Modules\Workflow\Enums\PaymentStatus;
use App\Modules\Workflow\Enums\RegistrationStatus;
use App\Modules\Workflow\Enums\StatusCategory;
use App\Shared\Actions\Action;
use App\Shared\Actions\DomainException;
use App\Shared\Auth\Permission;
use App\Shared\Money\Money;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * US-12: manual record of a bank transfer (amount, date, who). Recomputes the payment
 * status: part → partially_paid, full → paid + PaymentSettled. A payment after "paid"
 * creates no second invoice (PaymentSettled is raised only on the transition).
 *
 * PROPOSAL: BO6 — an overpayment is recorded and returned to the admin as a warning;
 * the refund happens outside the system.
 */
final class RecordPayment extends Action
{
    public function __construct(
        private readonly ChangeStatus $changeStatus,
        private readonly GetPaymentBalance $balance,
    ) {
    }

    public static function rules(): array
    {
        return [
            'amount' => ['required', 'string', 'regex:/^\d{1,9}([.,]\d{1,2})?$/'],
            'paid_on' => ['required', 'date', 'before_or_equal:today'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @param  array{amount:string, paid_on:string, note?:string|null}  $input
     * @return array{payment: Payment, balance: PaymentBalance}
     */
    public function handle(Authenticatable $actor, Registration $registration, array $input): array
    {
        $this->authorize($actor, Permission::PaymentsRecord, $registration->conference_id);
        $data = $this->validate(['amount' => (string) ($input['amount'] ?? '')] + $input);

        $amount = Money::parse($data['amount'], $registration->currency);
        if (! $amount->isPositive()) {
            throw ValidationException::withMessages(['amount' => __('payments.amount_positive')]);
        }

        return DB::transaction(function () use ($actor, $registration, $data, $amount) {
            $locked = Registration::query()->lockForUpdate()->findOrFail($registration->id);

            if ($locked->status === RegistrationStatus::Cancelled) {
                throw new DomainException(__('payments.registration_cancelled'));
            }

            if ($locked->payment_status === null) {
                throw new DomainException(__('payments.no_payment_required'));
            }

            $payment = Payment::query()->create([
                'conference_id' => $locked->conference_id,
                'registration_id' => $locked->id,
                'amount_cents' => $amount->cents,
                'currency' => $amount->currency,
                'paid_on' => $data['paid_on'],
                'variable_symbol' => $locked->variable_symbol,
                'recorded_by' => $actor->getAuthIdentifier(),
                'note' => $data['note'] ?? null,
            ]);

            $balance = $this->balance->handle($locked);
            $target = $balance->remaining->isZero() ? PaymentStatus::Paid : PaymentStatus::PartiallyPaid;

            if ($locked->payment_status !== $target && $locked->payment_status !== PaymentStatus::Paid) {
                $this->changeStatus->handle($locked, StatusCategory::Payment, $target, $actor, 'Payment '.$amount->format());

                if ($target === PaymentStatus::Paid) {
                    PaymentSettled::dispatch($locked->id, $locked->conference_id);
                }
            }

            PaymentRecorded::dispatch($payment->id, $locked->id, $locked->conference_id);

            $registration->setRawAttributes($locked->getAttributes(), true);

            return ['payment' => $payment, 'balance' => $balance];
        });
    }
}
