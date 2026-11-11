<?php

namespace App\Modules\Registration\Actions;

use App\Modules\Registration\Models\Registration;
use App\Shared\Actions\Action;

/**
 * Writes the fee waiver property of a registration (R32: a property, not a status).
 * Called only by Payments\GrantFeeWaiver, which checks the permission; this keeps
 * the registrations table written by the Registration module only.
 */
final class SetFeeWaived extends Action
{
    public function handle(Registration $registration, ?string $note): void
    {
        $registration->forceFill(['fee_waived' => true, 'fee_waiver_note' => $note])->save();
    }
}
