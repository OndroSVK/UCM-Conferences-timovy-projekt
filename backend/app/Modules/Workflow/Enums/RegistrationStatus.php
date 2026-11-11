<?php

namespace App\Modules\Workflow\Enums;

/** backend.md 7.1 */
enum RegistrationStatus: string implements WorkflowStatus
{
    case Received = 'received';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';

    public static function transitions(): array
    {
        return [
            'received' => ['confirmed', 'cancelled'],
            'confirmed' => ['cancelled'],
        ];
    }

    public static function initialStates(): array
    {
        return [self::Received];
    }
}
