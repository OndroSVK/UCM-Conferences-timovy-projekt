<?php

namespace App\Modules\Notifications\Enums;

enum EmailType: string
{
    case RegistrationConfirmation = 'registration_confirmation';
    case PaymentReceived = 'payment_received';
    case SubmissionReceived = 'submission_received';
    case Decision = 'decision';
    case ParticipationConfirmed = 'participation_confirmed';
}
