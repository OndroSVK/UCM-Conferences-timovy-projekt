<?php

namespace App\Modules\ParticipantPortal\Actions;

use App\Modules\Invoicing\Enums\InvoiceStatus;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Payments\Actions\GetPaymentBalance;
use App\Modules\Registration\Models\Registration;
use App\Modules\Submissions\Models\Submission;
use App\Shared\Actions\Action;
use App\Shared\Conference\ConferenceContext;
use App\Shared\Modules\Modules;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * US-08: detail of one own registration — three statuses, category, price, paid,
 * remaining, invoices, submission with versions and decisions for the author,
 * payment instructions. A registration of someone else is "not found".
 */
final class GetMyRegistrationDetail extends Action
{
    public function __construct(
        private readonly ConferenceContext $context,
        private readonly GetPaymentBalance $balance,
        private readonly GetPaymentInstructions $instructions,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(Authenticatable $actor, int $registrationId): array
    {
        $userId = (int) $actor->getAuthIdentifier();

        return $this->context->runAsOwner($userId, function () use ($actor, $userId, $registrationId) {
            $registration = Registration::query()
                ->where('user_id', $userId)
                ->with(['conference', 'category', 'profile'])
                ->find($registrationId);

            if ($registration === null) {
                throw (new ModelNotFoundException)->setModel(Registration::class, [$registrationId]);
            }

            $submission = Submission::query()
                ->where('registration_id', $registration->id)
                ->with(['versions', 'decisions'])
                ->first();

            // Invoicing may be switched off (O32); the portal works without it.
            $invoices = Modules::enabled('Invoicing')
                ? Invoice::query()
                    ->where('registration_id', $registration->id)
                    ->where('status', InvoiceStatus::Issued->value)
                    ->get()
                : collect();

            return [
                'registration' => $registration,
                'conference' => $registration->conference,
                'statuses' => [
                    'registration' => $registration->status,
                    'payment' => $registration->payment_status,
                    'submission' => $submission?->status,
                ],
                'balance' => $this->balance->handle($registration),
                'payment_instructions' => $this->instructions->handle($actor, $registration),
                'invoices' => $invoices,
                'submission' => $submission,
            ];
        });
    }
}
