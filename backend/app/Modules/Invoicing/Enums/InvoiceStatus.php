<?php

namespace App\Modules\Invoicing\Enums;

enum InvoiceStatus: string
{
    case Issued = 'issued';
    /** Issuing failed; the admin can retry (R24). The payment stays valid. */
    case Failed = 'failed';
}
