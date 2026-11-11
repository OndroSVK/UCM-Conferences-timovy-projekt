<?php

namespace App\Modules\Invoicing;

use App\Modules\Invoicing\Listeners\IssueInvoiceOnPaymentSettled;
use App\Modules\Payments\Events\PaymentSettled;
use App\Shared\Modules\ModuleServiceProvider;

/**
 * M8 — test invoices. Owner: A. O32 is open: build nothing else on invoices and keep
 * the module switchable (config/modules.php) without impact on payments.
 */
final class InvoicingServiceProvider extends ModuleServiceProvider
{
    protected function listeners(): array
    {
        return [
            PaymentSettled::class => [IssueInvoiceOnPaymentSettled::class],
        ];
    }

    protected function viewNamespace(): ?string
    {
        return 'invoicing';
    }
}
