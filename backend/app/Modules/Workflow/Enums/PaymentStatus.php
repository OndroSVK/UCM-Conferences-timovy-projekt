<?php

namespace App\Modules\Workflow\Enums;

/**
 * backend.md 7.2. All six customer states exist (R33); the prototype allows only
 * pending → partially_paid → paid. Fee waiver is a property, not a status (R32).
 */
enum PaymentStatus: string implements WorkflowStatus
{
    case Pending = 'pending';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    /** Full version. */
    case Overdue = 'overdue';
    /** Full version. */
    case Cancelled = 'cancelled';
    /** Full version. */
    case Refunded = 'refunded';

    public static function transitions(): array
    {
        return [
            // PROPOSAL: BO4 — pending → "" (no payment status) when an organizer grants a fee waiver.
            'pending' => ['partially_paid', 'paid', ''],
            'partially_paid' => ['paid'],
        ];
    }

    public static function initialStates(): array
    {
        return [self::Pending];
    }
}
