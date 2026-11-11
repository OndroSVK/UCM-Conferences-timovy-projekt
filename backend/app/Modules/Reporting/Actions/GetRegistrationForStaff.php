<?php

namespace App\Modules\Reporting\Actions;

use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Payments\Actions\GetPaymentBalance;
use App\Modules\Payments\Models\Payment;
use App\Modules\Registration\Models\Registration;
use App\Modules\Submissions\Models\Submission;
use App\Modules\Workflow\Actions\GetStatusHistory;
use App\Shared\Actions\Action;
use App\Shared\Auth\AccessControl;
use App\Shared\Auth\Permission;
use App\Shared\Conference\ConferenceContext;
use App\Shared\Modules\Modules;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Admin detail of one registration of the ACTIVE conference (read-only, added for the
 * admin UI). Financial data only with registrations.view_finance (never for the committee).
 */
final class GetRegistrationForStaff extends Action
{
    public function __construct(
        private readonly ConferenceContext $context,
        private readonly AccessControl $access,
        private readonly GetPaymentBalance $balance,
        private readonly GetStatusHistory $history,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(Authenticatable $actor, int $registrationId): array
    {
        $conferenceId = $this->context->requireConferenceId();
        $this->authorize($actor, Permission::RegistrationsView, $conferenceId);
        $finance = $this->access->can($actor, Permission::RegistrationsViewFinance, $conferenceId);

        $registration = Registration::query()->with(['profile', 'user', 'conference'])->findOrFail($registrationId);
        $submission = Submission::query()->where('registration_id', $registration->id)->with(['versions', 'decisions'])->first();

        return [
            'registration' => $registration,
            'submission' => $submission,
            'finance' => $finance,
            'balance' => $finance ? $this->balance->handle($registration) : null,
            'payments' => $finance ? Payment::query()->where('registration_id', $registration->id)->with('recorder')->orderBy('paid_on')->get() : collect(),
            'invoices' => $finance && Modules::enabled('Invoicing')
                ? Invoice::query()->where('registration_id', $registration->id)->get()
                : collect(),
            'history' => $this->history->handle($actor, $registration)->load('actor'),
        ];
    }
}
