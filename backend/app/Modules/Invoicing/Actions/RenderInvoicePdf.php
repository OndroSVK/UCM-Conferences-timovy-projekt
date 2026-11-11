<?php

namespace App\Modules\Invoicing\Actions;

use App\Modules\Conferences\Models\Conference;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Registration\Models\Registration;
use App\Shared\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * HTML template → PDF (dompdf). Every page is crossed by a red diagonal cross and
 * marked TEST INVOICE (R20, R26): the document can never pass as a real invoice.
 *
 * Numbering, mandatory fields and VAT (O29) are NOT verified; the faculty's economic
 * department must confirm them before production.
 *
 * Not final: tests replace it to simulate a rendering failure (R24).
 */
class RenderInvoicePdf extends Action
{
    public const WATERMARK = 'TEST INVOICE';

    public function handle(Invoice $invoice, Registration $registration, Conference $conference): string
    {
        $registration->loadMissing('profile');

        $pdf = Pdf::loadView('invoicing::test-invoice', [
            'invoice' => $invoice,
            'registration' => $registration,
            'conference' => $conference,
            'watermark' => self::WATERMARK,
        ])->setPaper('a4');

        $dompdf = $pdf->getDomPDF();
        $dompdf->getOptions()->setIsFontSubsettingEnabled(true); // embed only used glyphs (small attachment)
        $dompdf->render();

        // Red cross corner to corner over the whole page, on every page.
        $dompdf->getCanvas()->page_script(function ($pageNumber, $pageCount, $canvas) {
            $width = $canvas->get_width();
            $height = $canvas->get_height();
            $red = [0.85, 0, 0];
            $canvas->line(0, 0, $width, $height, $red, 4);
            $canvas->line($width, 0, 0, $height, $red, 4);
        });

        // Uncompressed streams keep the watermark text searchable in the file.
        return (string) $dompdf->output(['compress' => 0]);
    }
}
