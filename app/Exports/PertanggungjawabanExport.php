<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export Excel laporan Pertanggungjawaban Pemakaian BBM.
 * Roda Tiga sengaja di-skip - laporan ini cuma Roda Empat & Roda Dua.
 *
 * $data yang diharapkan:
 * [
 *   'bulanLabel'    => string,
 *   'weeks'         => [
 *       ['no' => int, 'periodeLabel' => string, 'groups' => array, 'grandTotal' => array],
 *       ...
 *   ],
 *   'keterangan'    => ['paiton' => float, ...],
 *   'penandatangan' => \App\Models\Penandatangan|null,
 * ]
 */
class PertanggungjawabanExport implements FromArray, WithEvents, WithTitle
{
    public function __construct(protected array $data)
    {
    }

    public function array(): array
    {
        return [['']];
    }

    public function title(): string
    {
        return 'Pertanggung Jawaban BBM';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->render($event->sheet->getDelegate());
            },
        ];
    }

    private function render(Worksheet $sheet): void
    {
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->setCellValue('A1', '');

        $this->setColumnWidths($sheet);

        $row = 1;
        $row = $this->writeMainTitle($sheet, $row);

        foreach ($this->data['weeks'] as $week) {
            $row = $this->writeWeekTitle($sheet, $row, $week['periodeLabel']);
            $row++;

            $groups = array_values(array_filter(
                $week['groups'],
                fn ($g) => !str_contains($g['label'], 'Roda Tiga')
            ));

            $groupCount = count($groups);
            $displayGrandTotal = ['liter' => 0, 'rp' => 0];
            foreach ($groups as $g) {
                $displayGrandTotal['liter'] += $g['total']['liter'];
                $displayGrandTotal['rp'] += $g['total']['rp'];
            }

            foreach ($groups as $index => $group) {
                $noUrut = $index + 1;
                $namaGroup = preg_replace('/^[A-Za-z]\.\s*/', '', $group['label']);

                $row = $this->writeGroupTitle($sheet, $row, $noUrut . '. ' . $namaGroup);
                $row = $this->writeColumnHeader($sheet, $row);

                foreach ($group['sections'] as $section) {
                    if ($section['label']) {
                        $row = $this->writeSectionLabel($sheet, $row, $section['label']);
                    }

                    foreach ($section['rows'] as $dataRow) {
                        $row = $this->writeDataRow($sheet, $row, $dataRow);
                    }
                }

                $row = $this->writeTotalRow($sheet, $row, 'Jumlah ' . $noUrut, $group['total']);

                if ($index === $groupCount - 1 && $groupCount > 0) {
                    $labelNums = implode(' + ', range(1, $groupCount));
                    $row = $this->writeTotalRow($sheet, $row, 'Jumlah ' . $labelNums, $displayGrandTotal);
                }
            }

            $row++;
        }

        $row = $this->writeKeterangan($sheet, $row);
        $this->writeSignature($sheet, $row);
    }

    private function setColumnWidths(Worksheet $sheet): void
    {
        // Dilebarkan dari sebelumnya (A6/B26/C14/D18)
        $widths = ['A' => 8, 'B' => 36, 'C' => 18, 'D' => 22];

        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
    }

    private function writeMainTitle(Worksheet $sheet, int $row): int
    {
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'PERTANGGUNGJAWABAN PEMAKAIAN BBM KENDARAAN DINAS');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(22);

        return $row + 1;
    }

    private function writeWeekTitle(Worksheet $sheet, int $row, string $periodeLabel): int
    {
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", "Periode {$periodeLabel}");
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(20);

        return $row + 1;
    }

    /**
     * Judul grup ("1. Roda Empat" dst) - plain bold text, tanpa fill.
     */
    private function writeGroupTitle(Worksheet $sheet, int $row, string $label): int
    {
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", $label);
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        return $row + 1;
    }

    /**
     * Header kolom - cuma 1 baris (No. / Nomor Kendaraan / Liter / Rp.),
     * baris nomor urut kolom (1,2,3,4) dihapus.
     */
    private function writeColumnHeader(Worksheet $sheet, int $row): int
    {
        $sheet->setCellValue("A{$row}", 'No.');
        $sheet->setCellValue("B{$row}", 'Nomor Kendaraan');
        $sheet->setCellValue("C{$row}", 'Liter');
        $sheet->setCellValue("D{$row}", 'Rp.');

        $range = "A{$row}:D{$row}";
        $sheet->getStyle($range)->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $this->applyBorder($sheet, $range);
        $sheet->getRowDimension($row)->setRowHeight(20);

        return $row + 1;
    }

    private function writeSectionLabel(Worksheet $sheet, int $row, string $label): int
    {
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", $label);
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $this->applyBorder($sheet, "A{$row}:D{$row}");

        return $row + 1;
    }

    private function writeDataRow(Worksheet $sheet, int $row, array $data): int
    {
        $sheet->setCellValue("A{$row}", $data['no']);
        $sheet->setCellValue("B{$row}", $data['plat_nomor']);
        $sheet->setCellValueExplicit("C{$row}", $data['liter'] ? number_format($data['liter'], 2, ',', '.') : '-', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("D{$row}", $data['rp'] ? number_format($data['rp'], 0, ',', '.') : '-', DataType::TYPE_STRING);

        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $this->applyBorder($sheet, "A{$row}:D{$row}");

        return $row + 1;
    }

    private function writeTotalRow(Worksheet $sheet, int $row, string $label, array $total): int
    {
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", $label);

        $sheet->setCellValueExplicit("C{$row}", number_format($total['liter'], 2, ',', '.'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("D{$row}", number_format($total['rp'], 0, ',', '.'), DataType::TYPE_STRING);

        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $this->applyBorder($sheet, "A{$row}:D{$row}");

        return $row + 1;
    }

    /**
     * Baris "Pemakaian BBM untuk di Paiton" - tanpa tanda "-" di depan, dan
     * "Rp <angka>" ditaruh langsung berdekatan (bukan di kolom terpisah jauh).
     */
    private function writeKeterangan(Worksheet $sheet, int $row): int
    {
        $k = $this->data['keterangan'];

        $sheet->setCellValue("A{$row}", 'Keterangan :');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'Laporan Pengeluaran BBM bulan ' . $this->data['bulanLabel']);
        $row += 2;

        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'Pemakaian BBM untuk di Paiton');
        $sheet->mergeCells("C{$row}:D{$row}");
        $sheet->setCellValue("C{$row}", 'Rp ' . number_format($k['paiton'], 0, ',', '.'));
        $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $row += 2;

        return $row;
    }

    /**
     * TTD ambil murni dari data penandatangan yang dikirim ke export ini
     * ($this->data['penandatangan'], instance \App\Models\Penandatangan|null).
     * $p sendiri bisa null (belum ada baris ASMAN di tabel), makanya semua
     * akses propertinya pakai null-safe operator (?->).
     */
    private function writeSignature(Worksheet $sheet, int $row): void
    {
        $p = $this->data['penandatangan'] ?? null;

        $tempat       = $p?->tempat ?? '';
        $tanggalLabel = now()->locale('id')->translatedFormat('d F Y');

        $sheet->mergeCells("C{$row}:D{$row}");
        $sheet->setCellValue("C{$row}", ($tempat ? $tempat . ', ' : '') . $tanggalLabel);
        $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        $sheet->mergeCells("C{$row}:D{$row}");
        $sheet->setCellValue("C{$row}", $p?->jabatan ? strtoupper($p->jabatan) : '...................................');
        $sheet->getStyle("C{$row}")->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row += 4;

        $sheet->mergeCells("C{$row}:D{$row}");
        $sheet->setCellValue("C{$row}", $p?->nama ?? '...................................');
        $sheet->getStyle("C{$row}")->applyFromArray([
            'font'      => ['bold' => true, 'underline' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    }

    private function applyBorder(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => '999999'],
                ],
            ],
        ]);
    }
}