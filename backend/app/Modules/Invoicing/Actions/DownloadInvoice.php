<?php

namespace App\Modules\Invoicing\Actions;

use App\Modules\Invoicing\Enums\InvoiceStatus;
use App\Modules\Invoicing\Models\Invoice;
use App\Shared\Actions\Action;
use App\Shared\Actions\DomainException;
use App\Shared\Auth\Permission;
use App\Shared\Files\FileDownload;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Download: owner of the registration, organizer or superadmin of the conference.
 * The programme committee has no invoices.view (no financial data).
 */
final class DownloadInvoice extends Action
{
    public function handle(Authenticatable $actor, Invoice $invoice): FileDownload
    {
        $registration = $invoice->registration()->firstOrFail();
        $this->authorizeOwnerOr($actor, $registration->user_id, Permission::InvoicesView, $invoice->conference_id);

        if ($invoice->status !== InvoiceStatus::Issued || $invoice->pdf_path === null) {
            throw new DomainException(__('invoices.not_available'));
        }

        return new FileDownload(
            (string) config('conferences.invoices.disk'),
            $invoice->pdf_path,
            $invoice->number.'.pdf',
            'application/pdf',
        );
    }
}
