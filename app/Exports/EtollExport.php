<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class EtollExport implements FromView, WithColumnFormatting, WithEvents
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

        // Hitung total kolom tanggal di seluruh bulan
        $totalDateCols = $this->totalDateCols();

        for ($i = 0; $i < $totalDateCols + 1; $i++) {
            $col = Coordinate::stringFromColumnIndex($i + 2);
            $formats[$col] = '#,##0';
        }

        return $formats;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $totalDateCols = $this->totalDateCols();

                // Kolom A = Nama
                $sheet->getColumnDimension('A')->setWidth(22);

                // Kolom tanggal (B s.d. sebelum Jumlah) - isinya cuma angka pendek
                for ($i = 0; $i < $totalDateCols; $i++) {
                    $col = Coordinate::stringFromColumnIndex($i + 2);
                    $sheet->getColumnDimension($col)->setWidth(6);
                }

                // Kolom Jumlah (terakhir)
                $lastCol = Coordinate::stringFromColumnIndex($totalDateCols + 2);
                $sheet->getColumnDimension($lastCol)->setWidth(14);

                // Baris header (1-7, sesuaikan kalau jumlah baris header beda) jangan wrap
                // biar label bulan/"Tanggal" nggak numpuk vertikal di kolom sempit
                $lastColLetter = $lastCol;
                $sheet->getStyle("A1:{$lastColLetter}7")
                    ->getAlignment()
                    ->setWrapText(false)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Biar teks label bulan yang lebih panjang dari kolomnya tetap kebaca
                // (numpuk ke kolom sebelah, bukan wrap ke bawah)
                $sheet->getStyle('A1:' . $lastColLetter . '7')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }

    private function totalDateCols(): int
    {
        $totalDateCols = 0;
        foreach ($this->data['bulanGroups'] as $group) {
            $totalDateCols += count($group['rows']);
        }

        return $totalDateCols;
    }
}