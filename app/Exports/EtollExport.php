<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class EtollExport implements FromView, WithColumnFormatting
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('exports.etoll-excel', $this->data);
    }

    public function columnFormats(): array
    {
        $formats = [];
        $maxDateCount = $this->data['maxDateCount'];

        for ($i = 0; $i < $maxDateCount + 1; $i++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 2);
            $formats[$col] = '#,##0';
        }

        return $formats;
    }
}
