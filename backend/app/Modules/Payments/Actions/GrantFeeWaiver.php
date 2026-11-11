<?php

namespace App\Modules\Payments\Actions;

use App\Modules\Payments\Models\Payment;
use App\Modules\Registration\Actions\SetFeeWaived;
use App\Modules\Registration\Models\Registration;
use App\Modules\Workflow\Actions\ChangeStatus;
use App\Modules\Workflow\Enums\PaymentStatus;
use App\Modules\Workflow\Enums\StatusCategory;
use App\Shared\Actions\Action;
use App\Shared\Actions\DomainException;
use App\Shared\Auth\Permission;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

/**
 * Individual fee waiver (a property, not a status; R32). The participant then gets no
 * payment request and no payment status (BO4: pending → null).
 *
 * PROPOSAL: BO4 — refused when a payment was already recorded (refunds are full version).
 * PROPOSAL: O38 — granted by an organizer with the delegated permission payments.waive.
 */
final class GrantFeeWaiver extends Action
{
    public function __construct(
        private readonly SetFeeWaived $setFeeWaived,
        private readonly ChangeStatus $changeStatus,
    ) {
    }

    public static function rules(): array
    {
        return ['note' => ['nullable', 'string', 'max:500']];
    }

    /**
     * @param  array{note?:string|null}  $input
     */
    public function handle(Authenticatable $actor, Registration $registration, array $input = []): void
    {
        $this->authorize($actor, Permission::PaymentsWaive, $registration->conference_id);
        $data = $this->validate($input);

        DB::transaction(function () use ($actor, $registration, $data) {
            $locked = Registration::query()->lockForUpdate()->findOrFail($registration->id);

            if ($locked->fee_waived) {
                throw new DomainException(__('payments.already_waived'));
            }

            if (Payment::query()->where('registration_id', $locked->id)->exists()) {
                throw new DomainException(__('payments.waiver_after_payment'));
            }

            $this->setFeeWaived->handle($locked, $data['note'] ?? null);

            if ($locked->payment_status === PaymentStatus::Pending) {
                $this->changeStatus->handle($locked, StatusCategory::Payment, null, $actor, 'Fee waiver');
            }

            $registration->setRawAttributes($locked->getAttributes(), true);
        });
    }
}
