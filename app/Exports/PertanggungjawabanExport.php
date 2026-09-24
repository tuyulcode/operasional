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
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export Excel laporan Pertanggungjawaban Pemakaian BBM.
 *
 * Semua jenis kendaraan (Roda Empat / Roda Tiga / Roda Dua) SELALU ditampilkan,
 * walaupun tidak ada datanya di periode ini - yang kosong tetap muncul dengan
 * isi strip.
 *
 * $data yang diharapkan:
 * [
 *   'weeks'                => [
 *       ['no' => int, 'periodeLabel' => string, 'groups' => array, 'grandTotal' => array],
 *       ...
 *   ],
 *   'keteranganBulanLabel' => string, // "September 2026" / "September & Oktober 2026" dst,
 *                                     // dihitung controller lewat formatBulanSajaLabel()
 *                                     // - dipakai buat teks "Laporan Pengeluaran BBM bulan ...".
 *   'keterangan'           => ['paiton' => float, ...],
 *   'penandatangan'        => \App\Models\Penandatangan|null,
 * ]
 */
class PertanggungjawabanExport implements FromArray, WithEvents, WithTitle
{
    /**
     * Warna aksen per jenis kendaraan - samain dengan tabel Rekapan.
     */
    private const GROUP_COLORS = [
        'A. Roda Empat' => 'BDD7EE', // biru muda
        'B. Roda Tiga'  => 'D9D2E9', // ungu muda
        'C. Roda Dua'   => 'C6E0B4', // hijau muda
    ];

    // Oranye - baris "Jumlah Total"
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
        $row++; // jarak biar judul file gak mepet sama tabel di bawahnya

        foreach ($this->data['weeks'] as $week) {
            $row = $this->writeWeekTitle($sheet, $row, $week['periodeLabel']);
            $row++;

            $groups = $this->normalizeGroups($week['groups']);

            $displayGrandTotal = ['liter' => 0, 'rp' => 0];
            foreach ($groups as $g) {
                $displayGrandTotal['liter'] += $g['total']['liter'];
                $displayGrandTotal['rp'] += $g['total']['rp'];
            }

            foreach ($groups as $index => $group) {
                $accentColor = self::GROUP_COLORS[$group['label']] ?? 'FFFFFF';
                $hurufGroup  = substr($group['label'], 0, 1); // "A. Roda Empat" -> "A"

                // Header kolom (No. / Nomor Kendaraan / Liter / Rp.) cuma muncul
                // sekali, DI ATAS banner grup pertama (Roda Empat).
                if ($index === 0) {
                    $row = $this->writeColumnHeader($sheet, $row);
                }

                $row = $this->writeGroupBanner($sheet, $row, $group['label'], $accentColor);

                if (empty($group['sections'])) {
                    // Jenis kendaraan ini tidak punya data - tetap ditampilkan, isinya strip
                    $row = $this->writeEmptyDataRow($sheet, $row);
                } else {
                    foreach ($group['sections'] as $section) {
                        if ($section['label']) {
                            $row = $this->writeSectionLabel($sheet, $row, $section['label']);
                        }

                        foreach ($section['rows'] as $dataRow) {
                            $row = $this->writeDataRow($sheet, $row, $dataRow);
                        }
                    }
                }

                $row = $this->writeTotalRow($sheet, $row, 'Subtotal ' . $hurufGroup, $group['total'], $accentColor);
            }

            $row = $this->writeTotalRow($sheet, $row, 'Jumlah Total', $displayGrandTotal, self::COLOR_GRAND_BG);

            $row++;
        }

        $row = $this->writeKeterangan($sheet, $row);
        $this->writeSignature($sheet, $row);
    }

    /**
     * Pastikan ketiga jenis kendaraan selalu ada & urut A-B-C. Yang tidak punya
     * data di periode ini diisi grup kosong (nanti dirender sebagai strip).
     */
    private function normalizeGroups(array $groups): array
    {
        $byLabel = [];
        foreach ($groups as $g) {
            $byLabel[$g['label']] = $g;
        }

        $hasil = [];
        foreach (array_keys(self::GROUP_COLORS) as $label) {
            $hasil[] = $byLabel[$label] ?? [
                'label'    => $label,
                'sections' => [],
                'total'    => ['liter' => 0, 'rp' => 0],
            ];
        }

        return $hasil;
    }

    private function setColumnWidths(Worksheet $sheet): void
    {
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
     * 1 baris kosong paling atas tabel - di-merge jadi satu sel, ada border,
     * tanpa warna. Row height dinaikkan sedikit biar ada jarak yang jelas
     * antara judul di atasnya dan tabel.
     */
    private function writeBlankTableRow(Worksheet $sheet, int $row): int
    {
        $sheet->mergeCells("A{$row}:D{$row}");
        $this->applyBorder($sheet, "A{$row}:D{$row}");
        $sheet->getRowDimension($row)->setRowHeight(10);

        return $row + 1;
    }

    /**
     * Banner jenis kendaraan ("A. Roda Empat" dst) - di-highlight sesuai warna grupnya.
     */
    private function writeGroupBanner(Worksheet $sheet, int $row, string $label, string $accentColor): int
    {
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", $label);
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $accentColor]],
        ]);
        $this->applyBorder($sheet, "A{$row}:D{$row}");

        return $row + 1;
    }

    /**
     * Header kolom (No. / Nomor Kendaraan / Liter / Rp.) - tanpa warna, cuma
     * ditulis sekali di atas grup pertama (Roda Empat).
     */
    private function writeColumnHeader(Worksheet $sheet, int $row): int
    {
        $sheet->setCellValue("A{$row}", 'No.');
        $sheet->setCellValue("B{$row}", 'Plat Nomor Kendaraan');
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

    /**
     * Baris strip buat jenis kendaraan yang tidak punya data di periode ini.
     */
    private function writeEmptyDataRow(Worksheet $sheet, int $row): int
    {
        foreach (['A', 'B', 'C', 'D'] as $col) {
            $sheet->setCellValueExplicit("{$col}{$row}", '-', DataType::TYPE_STRING);
        }

        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $this->applyBorder($sheet, "A{$row}:D{$row}");

        return $row + 1;
    }

    private function writeTotalRow(Worksheet $sheet, int $row, string $label, array $total, string $bgColor): int
    {
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", $label);

        $sheet->setCellValueExplicit("C{$row}", number_format($total['liter'], 2, ',', '.'), DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("D{$row}", number_format($total['rp'], 0, ',', '.'), DataType::TYPE_STRING);

        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
        ]);
        $sheet->getStyle("A{$row}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 1],
        ]);
        $this->applyBorder($sheet, "A{$row}:D{$row}");

        return $row + 1;
    }

    /**
     * Baris "Pemakaian BBM untuk di Paiton :" - dan teks "Laporan Pengeluaran
     * BBM bulan ..." yang labelnya dihitung otomatis dari tanggalMulai &
     * tanggalSelesai (lihat buildBulanLabel()).
     */
    private function writeKeterangan(Worksheet $sheet, int $row): int
    {
        $k          = $this->data['keterangan'];
        $bulanLabel = $this->data['keteranganBulanLabel'];

        $sheet->setCellValue("A{$row}", 'Keterangan :');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'Laporan Pengeluaran BBM bulan ' . $bulanLabel);
        $row += 2;

        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'Pemakaian BBM untuk di Paiton :');
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