<?php

namespace App\Shared\Auth;

/**
 * Fine-grained permissions (backend.md 4.2 and 9). Roles are bundles of these.
 */
enum Permission: string
{
    /** Basic registration data: name, titles, organization, e-mail, category. */
    case RegistrationsView = 'registrations.view';
    /** Prices, payments, billing data, invoices. Never for the programme committee. */
    case RegistrationsViewFinance = 'registrations.view_finance';
    case RegistrationsChangeStatus = 'registrations.change_status';
    case PaymentsRecord = 'payments.record';
    case PaymentsWaive = 'payments.waive';
    case InvoicesView = 'invoices.view';
    case InvoicesRetry = 'invoices.retry';
    case SubmissionsView = 'submissions.view';
    case SubmissionsDownload = 'submissions.download';
    case SubmissionsDecide = 'submissions.decide';
    case ExportsRegistrations = 'exports.registrations';
    case ConferencesAssignAdmins = 'conferences.assign_admins';

    /**
     * Permissions a superadmin may add to an organizer of one conference ("poverený organizátor").
     *
     * @return list<self>
     */
    public static function delegable(): array
    {
        return [self::PaymentsRecord, self::PaymentsWaive, self::InvoicesRetry];
    }

    public function isDelegable(): bool
    {
        return in_array($this, self::delegable(), true);
    }
}
