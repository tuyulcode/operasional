<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PemakaianBbmExport implements FromArray, WithEvents, WithTitle
{
    // Warna aksen per grup, urut: A. Roda Empat, B. Roda Tiga, C. Roda Dua
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
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->setCellValue('A1', '');

        $this->setColumnWidths($sheet);
        $this->writeLogo($sheet);

        $row = 1;
        $row = $this->writeTitleBlock($sheet, $row);
        $row++;

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
            'A' => 5, 'B' => 24, 'C' => 12, 'D' => 14,
            'E' => 20, 'F' => 12, 'G' => 16,
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
        $sheet->mergeCells("B{$row}:G{$row}");
        $sheet->setCellValue("B{$row}", 'PEMAKAIAN BBM KENDARAAN DINAS');
        $sheet->getStyle("B{$row}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(22);
        $row++;

        $sheet->mergeCells("B{$row}:G{$row}");
        $sheet->setCellValue("B{$row}", 'Periode ' . $this->data['periodeLabel']);
        $sheet->getStyle("B{$row}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row++;

        return $row;
    }

    private function writeGroupBanner(Worksheet $sheet, int $row, string $label): int
    {
        $sheet->mergeCells("A{$row}:G{$row}");
        $sheet->setCellValue("A{$row}", $label);
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $this->applyBorder($sheet, "A{$row}:G{$row}");

        return $row + 1;
    }

    private function writeColumnHeader(Worksheet $sheet, int $row, string $accentColor): int
    {
        $r1 = $row;
        $r2 = $row + 1;

        $sheet->mergeCells("A{$r1}:A{$r1}");
        $sheet->setCellValue("A{$r1}", 'No.');

        $sheet->setCellValue("B{$r1}", "Nomor Kendaraan");
        $sheet->getStyle("B{$r1}")->getAlignment()->setWrapText(true);

        $sheet->setCellValue("C{$r1}", 'Liter');
        $sheet->setCellValue("D{$r1}", 'Rp.');
        $sheet->setCellValue("E{$r1}", 'Sparepart Consumable');
        $sheet->setCellValue("F{$r1}", 'Jasa');
        $sheet->setCellValue("G{$r1}", 'Jumlah');

        $numbers = ['A' => '1', 'B' => '2', 'C' => '3', 'D' => '4', 'E' => '5', 'F' => '6', 'G' => '7 = 4+5+6'];
        foreach ($numbers as $col => $val) {
            $sheet->setCellValue("{$col}{$r2}", $val);
        }

        $range = "A{$r1}:G{$r2}";
        $sheet->getStyle($range)->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $accentColor]],
        ]);
        $this->applyBorder($sheet, $range);
        $sheet->getRowDimension($r1)->setRowHeight(24);
        $sheet->getRowDimension($r2)->setRowHeight(18);

        return $r2 + 1;
    }

    private function writeSectionLabel(Worksheet $sheet, int $row, string $label, string $accentColor): int
    {
        $sheet->mergeCells("A{$row}:G{$row}");
        $sheet->setCellValue("A{$row}", $label);
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $accentColor]],
        ]);
        $this->applyBorder($sheet, "A{$row}:G{$row}");

        return $row + 1;
    }

    private function writeDataRow(Worksheet $sheet, int $row, array $data): int
    {
        $sheet->setCellValue("A{$row}", $data['no']);
        $sheet->setCellValue("B{$row}", $data['plat_nomor']);
        $sheet->setCellValueExplicit("C{$row}", $data['liter'] ? number_format($data['liter'], 2, ',', '.') : '-', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("D{$row}", $data['rp'] ? number_format($data['rp'], 0, ',', '.') : '-', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("E{$row}", $data['service_oli'] ? number_format($data['service_oli'], 0, ',', '.') : '-', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("F{$row}", $data['jasa'] ? number_format($data['jasa'], 0, ',', '.') : '-', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("G{$row}", $data['jumlah'] ? number_format($data['jumlah'], 0, ',', '.') : '-', DataType::TYPE_STRING);

        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $this->applyBorder($sheet, "A{$row}:G{$row}");

        return $row + 1;
    }

    private function writeTotalRow(Worksheet $sheet, int $row, string $label, array $total, string $bgColor): int
    {
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", $label);

        $sheet->setCellValueExplicit("C{$row}", number_format($total['liter'], 2, ',', '.'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("D{$row}", number_format($total['rp'], 0, ',', '.'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("E{$row}", $total['service_oli'] ? number_format($total['service_oli'], 0, ',', '.') : '-', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("F{$row}", $total['jasa'] ? number_format($total['jasa'], 0, ',', '.') : '-', DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("G{$row}", number_format($total['jumlah'], 0, ',', '.'), DataType::TYPE_STRING);

        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
        ]);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $this->applyBorder($sheet, "A{$row}:G{$row}");

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