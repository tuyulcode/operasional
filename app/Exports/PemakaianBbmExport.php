<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PemakaianBbmExport implements FromArray, WithEvents, WithTitle
{
    // Warna aksen per grup, urut sesuai urutan Roda Empat > Roda Tiga > Roda Dua
    private const GROUP_COLORS = [
        'BDD7EE', // A. Roda Empat  - biru muda
        'D9D2E9', // B. Roda Tiga   - ungu muda
        'C6E0B4', // C. Roda Dua    - hijau muda
    ];

    // Oranye - baris total gabungan "Jumlah A+B+C"
    private const COLOR_GRAND_BG = 'FFC000';

    public function __construct(protected array $data)
    {
    }

    /**
     * Sheet diisi manual di event AfterSheet, jadi di sini cukup array kosong.
     */
    public function array(): array
    {
        return [['']];
    }

    public function title(): string
    {
        return 'Rekap BBM';
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
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->setCellValue('A1', '');

        $this->setColumnWidths($sheet);
        $this->writeLogo($sheet);

        $row = 1;
        $row = $this->writeTitleBlock($sheet, $row);
        $row++; // baris kosong spacer di bawah judul (sebelum tabel pertama)

        foreach ($this->data['groups'] as $index => $group) {
            $accentColor = self::GROUP_COLORS[$index] ?? self::GROUP_COLORS[0];

            $row = $this->writeGroupBanner($sheet, $row, $group['label']);
            $row = $this->writeColumnHeader($sheet, $row, $accentColor);

            foreach ($group['sections'] as $section) {
                if ($section['label']) {
                    $row = $this->writeSectionLabel($sheet, $row, $section['label'], $accentColor);
                }

                foreach ($section['rows'] as $dataRow) {
                    $row = $this->writeDataRow($sheet, $row, $dataRow);
                }
            }

            $row = $this->writeTotalRow(
                $sheet,
                $row,
                'Jumlah ' . substr($group['label'], 0, 1),
                $group['total'],
                $accentColor
            );
            // TIDAK ada spacer di sini - grup berikutnya langsung nyambung
        }

        if (!empty($this->data['groups'])) {
            $labels = implode('+', array_map(fn ($g) => substr($g['label'], 0, 1), $this->data['groups']));

            $this->writeTotalRow(
                $sheet,
                $row,
                'Jumlah ' . $labels,
                $this->data['grandTotal'],
                self::COLOR_GRAND_BG
            );
        }
    }

    private function setColumnWidths(Worksheet $sheet): void
    {
        $widths = [
            'A' => 5, 'B' => 16, 'C' => 10, 'D' => 12,
            'E' => 10, 'F' => 12, 'G' => 14, 'H' => 10, 'I' => 14,
        ];

        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
    }

    private function writeLogo(Worksheet $sheet): void
    {
        $path = public_path('images/logo-pln2.png');

        if (!file_exists($path)) {
            return;
        }

        $drawing = new Drawing();
        $drawing->setName('Logo PLN');
        $drawing->setPath($path);
        $drawing->setHeight(45);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(4);
        $drawing->setOffsetY(4);
        $drawing->setWorksheet($sheet);
    }

    private function writeTitleBlock(Worksheet $sheet, int $row): int
    {
        $sheet->mergeCells("B{$row}:I{$row}");
        $sheet->setCellValue("B{$row}", 'PEMAKAIAN BBM KENDARAAN DINAS & JASA');
        $sheet->getStyle("B{$row}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(22);
        $row++;

        $sheet->mergeCells("B{$row}:I{$row}");
        $sheet->setCellValue("B{$row}", 'Periode ' . $this->data['periodeLabel']);
        $sheet->getStyle("B{$row}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row++;

        return $row;
    }

    /**
     * Banner "A. Roda Empat" dst - putih polos, cuma bold + border.
     */
    private function writeGroupBanner(Worksheet $sheet, int $row, string $label): int
    {
        $sheet->mergeCells("A{$row}:I{$row}");
        $sheet->setCellValue("A{$row}", $label);
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $this->applyBorder($sheet, "A{$row}:I{$row}");

        return $row + 1;
    }

    /**
     * Header kolom (No., No Kendaraan, Pengisian..., 1-9) - warna sesuai grup, bold + border.
     */
    private function writeColumnHeader(Worksheet $sheet, int $row, string $accentColor): int
    {
        $r1 = $row;
        $r2 = $row + 1;
        $r3 = $row + 2;
        $r4 = $row + 3;

        $sheet->mergeCells("A{$r1}:A{$r3}");
        $sheet->setCellValue("A{$r1}", 'No.');

        $sheet->mergeCells("B{$r1}:B{$r3}");
        $sheet->setCellValue("B{$r1}", "No.\nKendaraan");
        $sheet->getStyle("B{$r1}")->getAlignment()->setWrapText(true);

        $sheet->mergeCells("C{$r1}:D{$r1}");
        $sheet->setCellValue("C{$r1}", 'Pengisian Di Paiton');

        $sheet->mergeCells("E{$r1}:F{$r1}");
        $sheet->setCellValue("E{$r1}", 'Pengisian Di Luar Paiton');

        $sheet->mergeCells("G{$r1}:G{$r3}");
        $sheet->setCellValue("G{$r1}", 'Service, Oli, dll');

        $sheet->mergeCells("H{$r1}:H{$r3}");
        $sheet->setCellValue("H{$r1}", 'Jasa');

        $sheet->mergeCells("I{$r1}:I{$r3}");
        $sheet->setCellValue("I{$r1}", 'Jumlah');

        $sheet->mergeCells("C{$r2}:D{$r2}");
        $sheet->setCellValue("C{$r2}", 'PREMIUM/SOLAR');

        $sheet->mergeCells("E{$r2}:F{$r2}");
        $sheet->setCellValue("E{$r2}", 'PREMIUM/SOLAR');

        $sheet->setCellValue("C{$r3}", 'Liter');
        $sheet->setCellValue("D{$r3}", 'Rp.');
        $sheet->setCellValue("E{$r3}", 'Liter');
        $sheet->setCellValue("F{$r3}", 'Rp.');

        $numbers = [
            'A' => '1', 'B' => '2', 'C' => '3', 'D' => '4', 'E' => '5',
            'F' => '6', 'G' => '7', 'H' => '8', 'I' => '9 = 4+6+7+8',
        ];
        foreach ($numbers as $col => $val) {
            $sheet->setCellValue("{$col}{$r4}", $val);
        }

        $range = "A{$r1}:I{$r4}";
        $sheet->getStyle($range)->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $accentColor]],
        ]);
        $this->applyBorder($sheet, $range);

        return $r4 + 1;
    }

    /**
     * Label "Unit 1-2" / "Unit 9" - warna sesuai grup (sama seperti header kolom grup itu).
     */
    private function writeSectionLabel(Worksheet $sheet, int $row, string $label, string $accentColor): int
    {
        $sheet->mergeCells("A{$row}:I{$row}");
        $sheet->setCellValue("A{$row}", $label);
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $accentColor]],
        ]);
        $this->applyBorder($sheet, "A{$row}:I{$row}");

        return $row + 1;
    }

    private function writeDataRow(Worksheet $sheet, int $row, array $data): int
    {
        $sheet->setCellValue("A{$row}", $data['no']);
        $sheet->setCellValue("B{$row}", $data['plat_nomor']);
        $sheet->setCellValue("C{$row}", $data['liter_paiton'] ? number_format($data['liter_paiton'], 2, ',', '.') : '-');
        $sheet->setCellValue("D{$row}", $data['rp_paiton'] ? number_format($data['rp_paiton'], 0, ',', '.') : '-');
        $sheet->setCellValue("E{$row}", $data['liter_luar_paiton'] ? number_format($data['liter_luar_paiton'], 2, ',', '.') : '-');
        $sheet->setCellValue("F{$row}", $data['rp_luar_paiton'] ? number_format($data['rp_luar_paiton'], 0, ',', '.') : '-');
        $sheet->setCellValue("G{$row}", $data['service_oli'] ? number_format($data['service_oli'], 0, ',', '.') : '-');
        $sheet->setCellValue("H{$row}", $data['jasa'] ? number_format($data['jasa'], 0, ',', '.') : '-');
        $sheet->setCellValue("I{$row}", $data['jumlah'] ? number_format($data['jumlah'], 0, ',', '.') : '-');

        $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $this->applyBorder($sheet, "A{$row}:I{$row}");

        return $row + 1;
    }

    /**
     * Baris "Jumlah A/B/C" (warna sesuai grup) atau "Jumlah A+B+C" (oranye).
     */
    private function writeTotalRow(Worksheet $sheet, int $row, string $label, array $total, string $bgColor): int
    {
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", $label);

        $sheet->setCellValue("C{$row}", number_format($total['liter_paiton'], 2, ',', '.'));
        $sheet->setCellValue("D{$row}", number_format($total['rp_paiton'], 0, ',', '.'));
        $sheet->setCellValue("E{$row}", number_format($total['liter_luar_paiton'], 2, ',', '.'));
        $sheet->setCellValue("F{$row}", number_format($total['rp_luar_paiton'], 0, ',', '.'));
        $sheet->setCellValue("G{$row}", $total['service_oli'] ? number_format($total['service_oli'], 0, ',', '.') : '-');
        $sheet->setCellValue("H{$row}", $total['jasa'] ? number_format($total['jasa'], 0, ',', '.') : '-');
        $sheet->setCellValue("I{$row}", number_format($total['jumlah'], 0, ',', '.'));

        $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
        ]);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $this->applyBorder($sheet, "A{$row}:I{$row}");

        return $row + 1;
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