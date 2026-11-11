<?php

namespace App\Modules\Workflow\Enums;

/**
 * The three independent statuses (R5): registration, payment, submission.
 */
enum StatusCategory: string
{
    case Registration = 'registration';
    case Payment = 'payment';
    case Submission = 'submission';

    /** Column on the subject model. */
    public function column(): string
    {
        return match ($this) {
            self::Registration => 'status',
            self::Payment => 'payment_status',
            self::Submission => 'status',
        };
    }

    /** @return class-string<WorkflowStatus&\BackedEnum> */
    public function enum(): string
    {
        return match ($this) {
            self::Registration => RegistrationStatus::class,
            self::Payment => PaymentStatus::class,
            self::Submission => SubmissionStatus::class,
        };
    }
}
