<?php

namespace App\Modules\Invoicing\Actions;

use App\Modules\Invoicing\Enums\InvoiceStatus;
use App\Modules\Invoicing\Models\Invoice;
use App\Shared\Actions\Action;
use App\Shared\Actions\DomainException;
use App\Shared\Auth\Permission;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * After a failure the admin issues the invoice again (R24). The payment stays valid.
 */
final class RetryInvoiceIssue extends Action
{
    public function __construct(private readonly IssueInvoice $issue)
    {
    }

    public function handle(Authenticatable $actor, Invoice $invoice): Invoice
    {
        $this->authorize($actor, Permission::InvoicesRetry, $invoice->conference_id);

        if ($invoice->status !== InvoiceStatus::Failed) {
            throw new DomainException(__('invoices.retry_only_failed'));
        }

        return $this->issue->handle($invoice->registration_id);
    }
}
