<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\UseAdminConference;
use App\Modules\Conferences\Actions\SwitchActiveConference;
use App\Modules\Invoicing\Actions\DownloadInvoice;
use App\Modules\Invoicing\Actions\RetryInvoiceIssue;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Payments\Actions\FindRegistrationByVariableSymbol;
use App\Modules\Payments\Actions\GrantFeeWaiver;
use App\Modules\Payments\Actions\RecordPayment;
use App\Modules\Registration\Models\Registration;
use App\Modules\Reporting\Actions\GetRegistrationForStaff;
use App\Modules\Reporting\Actions\ListConferenceRegistrations;
use App\Modules\Workflow\Actions\ChangeStatus;
use App\Modules\Workflow\Enums\RegistrationStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Admin: registrations, payments, fee waivers, registration status, invoices. */
final class RegistrationController extends Controller
{
    public function index(Request $request, ListConferenceRegistrations $list)
    {
        return view('admin.registrations.index', [
            'registrations' => $list->handle($request->user(), $request->only(['q', 'status', 'category'])),
            'filters' => $request->only(['q', 'status', 'category']),
        ]);
    }

    public function switchConference(Request $request, SwitchActiveConference $switch)
    {
        $conference = $switch->handle($request->user(), (int) $request->input('conference_id'));
        $request->session()->put(UseAdminConference::SESSION_KEY, $conference->id);

        return redirect()->route('admin.registrations.index');
    }

    public function show(Request $request, GetRegistrationForStaff $detail, int $registration)
    {
        return view('admin.registrations.show', $detail->handle($request->user(), $registration));
    }

    public function findByVariableSymbol(Request $request, FindRegistrationByVariableSymbol $find)
    {
        $registration = $find->handle($request->user(), (string) $request->query('vs', ''));

        if ($registration === null) {
            return back()->withErrors(['vs' => __('ui.admin.vs_not_found')])->withInput();
        }

        return redirect()->route('admin.registrations.show', $registration->id);
    }

    public function recordPayment(Request $request, RecordPayment $record, int $registration)
    {
        ['balance' => $balance] = $record->handle(
            $request->user(),
            Registration::query()->findOrFail($registration),
            $request->only(['amount', 'paid_on', 'note']),
        );

        $redirect = redirect()->route('admin.registrations.show', $registration)->with('status', __('ui.admin.payment_recorded'));

        // PROPOSAL BO6: overpayment is shown as a warning
        return $balance->overpaid->isPositive()
            ? $redirect->with('warning', __('payments.overpaid', ['amount' => $balance->overpaid->format()]))
            : $redirect;
    }

    public function waiveFee(Request $request, GrantFeeWaiver $waive, int $registration)
    {
        $waive->handle($request->user(), Registration::query()->findOrFail($registration), $request->only('note'));

        return redirect()->route('admin.registrations.show', $registration)->with('status', __('ui.admin.fee_waived'));
    }

    public function changeStatus(Request $request, ChangeStatus $change, int $registration)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([RegistrationStatus::Confirmed->value, RegistrationStatus::Cancelled->value])],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $change->byStaff(
            $request->user(),
            Registration::query()->findOrFail($registration),
            RegistrationStatus::from($data['status']),
            $data['note'] ?? null,
        );

        return redirect()->route('admin.registrations.show', $registration)->with('status', __('ui.admin.status_changed'));
    }

    public function retryInvoice(Request $request, RetryInvoiceIssue $retry, int $invoice)
    {
        $model = $retry->handle($request->user(), Invoice::query()->findOrFail($invoice));

        return redirect()->route('admin.registrations.show', $model->registration_id)->with('status', __('ui.admin.invoice_retried'));
    }

    public function downloadInvoice(Request $request, DownloadInvoice $download, int $invoice)
    {
        return $this->sendFile($download->handle($request->user(), Invoice::query()->findOrFail($invoice)));
    }
}
