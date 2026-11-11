<?php

namespace App\Modules\Reporting\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Plain rows prepared by ExportRegistrations; the export itself contains no logic.
 */
final class RegistrationsExport implements FromArray, WithHeadings
{
    /**
     * @param  list<string>  $headings
     * @param  list<list<scalar|null>>  $rows
     */
    public function __construct(
        private readonly array $headings,
        private readonly array $rows,
    ) {
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return $this->rows;
    }
}
