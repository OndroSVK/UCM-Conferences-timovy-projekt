<?php

namespace App\Modules\Invoicing\Enums;

enum InvoiceType: string
{
    case Invoice = 'invoice';
    /** Full version: a correction of an issued invoice. */
    case CreditNote = 'credit_note';
}
