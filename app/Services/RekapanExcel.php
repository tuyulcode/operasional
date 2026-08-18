<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapanExcel
{
    private const HEADER_FILL = 'FFd9e2f3';

    private const GRAND_FILL = 'FFe2efda';

    public static function generate(array $report): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        $used = [];
        foreach ($report['data'] as $area) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle(self::sheetTitle($area['area']->nama, $used));
            self::fill($sheet, $area, $report['periodeLabel'] ?? '');
        }

        if ($spreadsheet->getSheetCount() === 0) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('Rekap');
            $sheet->setCellValue('A1', 'Tidak ada data untuk periode ini.');
        }

        return $spreadsheet;
    }

    private static function sheetTitle(string $name, array &$used): string
    {
        $base = trim(str_replace(['\\', '/', '*', '?', ':', '[', ']'], '-', $name));
        $base = mb_substr($base === '' ? 'Area' : $base, 0, 31);
        $title = $base;
        $i = 1;
        while (in_array(mb_strtolower($title), $used, true)) {
            $suffix = ' '.$i++;
            $title = mb_substr($base, 0, 31 - mb_strlen($suffix)).$suffix;
        }
        $used[] = mb_strtolower($title);

        return $title;
    }

    private static function fill(Worksheet $sheet, array $area, string $periodeLabel): void
    {
        if (($area['jml_titik'] ?? $area['rows']->count()) === 1) {
            self::vertical($sheet, $area, $periodeLabel);
        } else {
            self::horizontal($sheet, $area);
        }
    }

    private static function vertical(Worksheet $sheet, array $area, string $periodeLabel): void
    {
        $row1 = $area['rows']->first();
        $tg = $row1['tagihan'] ?? null;
        $ini = $tg ? (int) round((float) $tg->meter_ini) : 0;
        $lalu = $tg ? (int) round((float) $tg->meter_lalu) : 0;
        $faktor = $tg ? (float) $tg->meter_faktor : 0;

        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(3);
        $sheet->getColumnDimension('C')->setWidth(30);

        $r = 1;
        self::titleRow($sheet, $r, 'BIAYA PEMAKAIAN AIR');
        self::kv($sheet, $r, 'Bulan', $periodeLabel);
        self::kv($sheet, $r, 'NAMA', $area['area']->nama);
        self::kv($sheet, $r, 'ALAMAT', $area['area']->alamat ?: '-');
        self::kv($sheet, $r, 'LOKASI FLOW METER', $row1['titik_meter']->nama);
        self::titleRow($sheet, $r, 'PERHITUNGAN PEMAKAIAN');
        self::kv($sheet, $r, 'Bulan ini', $ini);
        self::kv($sheet, $r, 'Bulan lalu', $lalu);
        self::kv($sheet, $r, 'Jumlah Pengambilan', $ini - $lalu);
        self::kv($sheet, $r, 'Meter Faktor', $tg ? number_format($faktor, 0, ',', '.') : '0');
        self::kv($sheet, $r, 'Jumlah Pengambilan', $tg ? (int) round((float) $tg->pemakaian) : 0);
        self::kv($sheet, $r, 'Tarif / M3', self::rp($tg->tarif ?? 0));
        self::kv($sheet, $r, 'Jumlah (Rp)', self::rp($area['subtotal']), true);
        if ($area['kena_ppn']) {
            self::kv($sheet, $r, 'PPN '.number_format($area['persen_ppn'], 0, ',', '.').'%', self::rp($area['ppn']));
            self::kv($sheet, $r, 'Jumlah (Rp)', self::rp($area['total']), true);
        }

        self::borders($sheet, 'A1:C'.($r - 1));
    }

    private static function horizontal(Worksheet $sheet, array $area): void
    {
        foreach (['A' => 8, 'B' => 32, 'C' => 12, 'D' => 12, 'E' => 14, 'F' => 18, 'G' => 22] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        self::hcell($sheet, 'A1', 'No. Urut', true);
        self::hcell($sheet, 'B1', 'Nama Titik Meter', true);
        $sheet->setCellValue('C1', 'COUNTER M3');
        $sheet->mergeCells('C1:D1');
        $sheet->getStyle('C1')->getFont()->setBold(true);
        $sheet->getStyle('C1:D1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::HEADER_FILL);
        self::hcell($sheet, 'E1', 'Pengambilan', true);
        self::hcell($sheet, 'F1', 'Tarif (Rp/M3)', true);
        self::hcell($sheet, 'G1', 'Jumlah (Rp)', true);
        self::hcell($sheet, 'C2', 'Bulan Ini', true);
        self::hcell($sheet, 'D2', 'Bulan Lalu', true);

        $r = 3;
        foreach ($area['rows'] as $i => $row) {
            if (! $row['tagihan']) {
                continue;
            }
            $tg = $row['tagihan'];
            $sheet->setCellValue('A'.$r, $i + 1);
            $sheet->setCellValue('B'.$r, $row['titik_meter']->nama);
            $sheet->setCellValue('C'.$r, (int) round((float) $tg->meter_ini));
            $sheet->setCellValue('D'.$r, (int) round((float) $tg->meter_lalu));
            $sheet->setCellValue('E'.$r, (int) round((float) $tg->pemakaian));
            $sheet->setCellValue('F'.$r, self::rp($tg->tarif));
            $sheet->setCellValue('G'.$r, self::rp($tg->jumlah));
            $r++;
        }

        $sheet->setCellValue('A'.$r, 'Subtotal '.$area['area']->nama);
        $sheet->mergeCells('A'.$r.':D'.$r);
        $sheet->getStyle('A'.$r)->getFont()->setBold(true);
        $sheet->setCellValue('E'.$r, $area['total_pemakaian'] ? (int) round($area['total_pemakaian']) : '-');
        $sheet->setCellValue('G'.$r, self::rp($area['subtotal']));
        $sheet->getStyle('G'.$r)->getFont()->setBold(true);
        $r++;

        if ($area['kena_ppn']) {
            $sheet->setCellValue('A'.$r, 'PPN '.number_format($area['persen_ppn'], 0, ',', '.').'%');
            $sheet->mergeCells('A'.$r.':D'.$r);
            $sheet->getStyle('A'.$r)->getFont()->setBold(true);
            $sheet->setCellValue('G'.$r, self::rp($area['ppn']));
            $r++;

            $sheet->setCellValue('A'.$r, 'Total '.$area['area']->nama);
            $sheet->mergeCells('A'.$r.':D'.$r);
            $sheet->getStyle('A'.$r)->getFont()->setBold(true);
            $sheet->setCellValue('G'.$r, self::rp($area['total']));
            $sheet->getStyle('G'.$r)->getFont()->setBold(true);
            $sheet->getStyle('A'.$r.':G'.$r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::GRAND_FILL);
            $r++;
        }

        self::borders($sheet, 'A1:G'.($r - 1));
    }

    private static function titleRow(Worksheet $sheet, int &$r, string $label): void
    {
        $sheet->setCellValue('A'.$r, $label);
        $sheet->mergeCells('A'.$r.':C'.$r);
        $sheet->getStyle('A'.$r)->getFont()->setBold(true);
        $r++;
    }

    private static function kv(Worksheet $sheet, int &$r, string $label, mixed $value, bool $bold = false): void
    {
        $sheet->setCellValue('A'.$r, $label);
        $sheet->setCellValue('B'.$r, ':');
        $sheet->setCellValue('C'.$r, $value);
        if ($bold) {
            $sheet->getStyle('A'.$r)->getFont()->setBold(true);
            $sheet->getStyle('C'.$r)->getFont()->setBold(true);
        }
        $r++;
    }

    private static function hcell(Worksheet $sheet, string $cell, string $value, bool $bold): void
    {
        $sheet->setCellValue($cell, $value);
        $style = $sheet->getStyle($cell);
        $style->getFont()->setBold($bold);
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::HEADER_FILL);
    }

    private static function borders(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getBorders()->applyFromArray([
            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF555555']],
        ]);
    }

    private static function rp(mixed $value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }
}
