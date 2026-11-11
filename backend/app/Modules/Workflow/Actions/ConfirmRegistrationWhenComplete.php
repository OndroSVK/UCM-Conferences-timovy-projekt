<?php

namespace App\Modules\Workflow\Actions;

use App\Modules\Registration\Models\Registration;
use App\Modules\Submissions\Models\Submission;
use App\Modules\Workflow\Enums\PaymentStatus;
use App\Modules\Workflow\Enums\RegistrationStatus;
use App\Modules\Workflow\Enums\StatusCategory;
use App\Modules\Workflow\Enums\SubmissionStatus;
use App\Shared\Actions\Action;
use Illuminate\Support\Facades\DB;

/**
 * received → confirmed when the payment is settled (paid, fee waiver or nothing to pay)
 * AND the submission is accepted or not required.
 *
 * PROPOSAL: BO3 — conditionally_accepted does not confirm automatically; an organizer
 * may confirm manually (ChangeStatus::byStaff).
 */
final class ConfirmRegistrationWhenComplete extends Action
{
    public function __construct(private readonly ChangeStatus $changeStatus)
    {
    }

    /** @return bool true when the registration was confirmed now */
    public function handle(int $registrationId): bool
    {
        return DB::transaction(function () use ($registrationId): bool {
            $registration = Registration::query()->lockForUpdate()->find($registrationId);

            if ($registration === null || $registration->status !== RegistrationStatus::Received) {
                return false;
            }

            $paymentSettled = $registration->payment_status === PaymentStatus::Paid
                || $registration->payment_status === null; // fee waiver or zero price (BO4)

            $submissions = Submission::query()->where('registration_id', $registration->id)->get();

            $submissionSettled = $submissions->isNotEmpty() && $submissions->every(
                fn (Submission $s) => in_array($s->status, [SubmissionStatus::Accepted, SubmissionStatus::NotRequired], true),
            );

            if (! $paymentSettled || ! $submissionSettled) {
                return false;
            }

            $this->changeStatus->handle(
                $registration,
                StatusCategory::Registration,
                RegistrationStatus::Confirmed,
                note: 'Confirmed automatically: payment settled and submission accepted / not required.',
            );

            return true;
        });
    }
}
