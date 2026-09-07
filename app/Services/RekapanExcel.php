<?php

namespace App\Services;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapanExcel
{
    private const HEADER_FILL = 'FFd9e2f3';

    private const GRAND_FILL = 'FFe2efda';

    private const PPN_FILL = 'FFE2EFDA';

    // Lebar kolom titik meter maksimum (dalam unit lebar Excel) biar kolom
    // gak kebablasan lebar kalau ada foto landscape yang ekstrim rasionya.
    private const MAX_TITIK_COL_WIDTH = 45;

    public static function generate(array $report): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        $penandatangan = $report['penandatangan'] ?? collect();

        $used = [];
        foreach ($report['data'] as $area) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle(self::sheetTitle($area['area']->nama, $used));
            self::fill($sheet, $area, $report['periodeLabel'] ?? '', $penandatangan);
        }

        if ($spreadsheet->getSheetCount() === 0) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('Rekap');
            self::headerBlock($sheet, '', 'A', 'BIAYA PEMAKAIAN AIR');
            $sheet->setCellValue('A6', 'Tidak ada data untuk periode ini.');
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

    private static function fill(Worksheet $sheet, array $area, string $periodeLabel, $penandatangan): void
    {
        $jmlTitik = $area['jml_titik'] ?? $area['rows']->count();

        if ($jmlTitik === 1) {
            self::vertical($sheet, $area, $periodeLabel, $penandatangan);
        } elseif ($jmlTitik <= 3) {
            self::horizontal($sheet, $area, $periodeLabel, $penandatangan);
        } else {
            self::list($sheet, $area, $periodeLabel, $penandatangan);
        }
    }

    private static function headerBlock(Worksheet $sheet, string $periodeLabel, string $lastCol, string $title = 'BIAYA PEMAKAIAN AIR'): int
    {
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);

        $sheet->getRowDimension(1)->setRowHeight(20);
        $sheet->getRowDimension(2)->setRowHeight(20);

        $logo = public_path('images/logo.png');
        if (is_file($logo)) {
            $drawing = new Drawing;
            $drawing->setName('logo');
            $drawing->setPath($logo);
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(4);
            $drawing->setOffsetY(4);
            $drawing->setResizeProportional(true);
            $drawing->setHeight(35);
            $drawing->setWorksheet($sheet);
        }

        $sheet->mergeCells('B1:D1');
        $sheet->setCellValue('B1', 'PT PLN NUSANTARA POWER');
        $sheet->getStyle('B1')->getFont()->setName('Calibri')->setBold(false)->setSize(10);
        $sheet->getStyle('B1')->getAlignment()->setVertical('center');

        $sheet->mergeCells('B2:D2');
        $sheet->setCellValue('B2', 'UNIT PEMBANGKITAN PAITON');
        $sheet->getStyle('B2')->getFont()->setName('Calibri')->setBold(false)->setSize(10);
        $sheet->getStyle('B2')->getAlignment()->setVertical('center');

        $r = 3;
        $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
        $sheet->setCellValue("A{$r}", $title);
        $sheet->getStyle("A{$r}")->getFont()->setName('Calibri')->setBold(true)->setSize(16);
        $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal('center')->setVertical('center');
        $sheet->getRowDimension($r)->setRowHeight(28);
        $r++;

        $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
        $sheet->setCellValue("A{$r}", $periodeLabel);
        $sheet->getStyle("A{$r}")->getFont()->setName('Calibri')->setBold(true)->setSize(12);
        $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal('center');
        $sheet->getRowDimension($r)->setRowHeight(20);
        $r++;

        $sheet->getRowDimension($r)->setRowHeight(8);
        $r++;

        return $r;
    }

    private static function vertical(Worksheet $sheet, array $area, string $periodeLabel, $penandatangan): void
    {
        $row1 = $area['rows']->first();
        $tg = $row1['tagihan'] ?? null;
        $ini = $tg ? (int) round((float) $tg->meter_ini) : 0;
        $lalu = $tg ? (int) round((float) $tg->meter_lalu) : 0;
        $faktor = $tg ? (float) $tg->meter_faktor : 0;

        $colWidthsPx = ['A' => 22, 'B' => 13, 'C' => 16, 'D' => 12];
        $sheet->getColumnDimension('A')->setWidth($colWidthsPx['A']);
        $sheet->getColumnDimension('B')->setWidth($colWidthsPx['B']);
        $sheet->getColumnDimension('C')->setWidth($colWidthsPx['C']);
        $sheet->getColumnDimension('D')->setWidth($colWidthsPx['D']);

        $r = self::headerBlock($sheet, $periodeLabel, 'D', 'BIAYA PEMAKAIAN AIR');
        $dataStart = $r;

        self::kv($sheet, $r, 'NAMA', $area['area']->nama, null, true);
        self::kv($sheet, $r, 'ALAMAT', $area['area']->alamat ?: '-');
        self::kv($sheet, $r, 'LOKASI FLOW METER', $row1['titik_meter']->nama);
        self::titleRow($sheet, $r, 'PERHITUNGAN PEMAKAIAN');
        self::kv($sheet, $r, 'Bulan ini', $ini, '( a )', false, 'M³');
        self::kv($sheet, $r, 'Bulan lalu', $lalu, '( b )', false, 'M³');
        self::kv($sheet, $r, 'Jumlah Pengambilan', $ini - $lalu, '( c = a - b )', false, 'M³');
        self::kv($sheet, $r, 'Meter Faktor', $tg ? number_format($faktor, 0, ',', '.') : '0', '( d )');
        self::kv($sheet, $r, 'Jumlah Pengambilan', $tg ? (int) round((float) $tg->pemakaian) : 0, '( e = c x d )', false, 'M³');
        self::kv($sheet, $r, 'Tarif / Harga', (float) ($tg->tarif ?? 0), 'Rp', false, null, 'D', '#,##0');
        self::kv($sheet, $r, 'Jumlah (Rp)', (float) $area['subtotal'], null, true, null, 'D', '#,##0');
        if ($area['kena_ppn']) {
            self::kv($sheet, $r, 'PPN '.number_format($area['persen_ppn'], 0, ',', '.').'%', (float) $area['ppn'], null, false, null, 'D', '#,##0');
            $sheet->getStyle('A'.($r - 1).':D'.($r - 1))
                ->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB(self::PPN_FILL);
            self::kv($sheet, $r, 'Jumlah (Rp)', (float) $area['total'], null, true, null, 'D', '#,##0');
        }

        self::borders($sheet, 'A'.$dataStart.':D'.($r - 1));
        $r++;

        $fotoPath = self::resolveFotoPath($tg);
        if ($fotoPath) {
            $drawing = new Drawing;
            $drawing->setName('foto-meter');
            $drawing->setPath($fotoPath);
            $drawing->setResizeProportional(true);
            $drawing->setHeight(110);

            $tableWidthPx = array_sum($colWidthsPx) * 7;
            if ($drawing->getWidth() + 10 > $tableWidthPx) {
                $extraPx = $drawing->getWidth() + 10 - $tableWidthPx;
                $colWidthsPx['D'] += (int) ceil($extraPx / 7);
                $sheet->getColumnDimension('D')->setWidth($colWidthsPx['D']);
                $tableWidthPx = array_sum($colWidthsPx) * 7;
            }

            $offsetX = max(5, (int) (($tableWidthPx - $drawing->getWidth()) / 2));

            $drawing->setCoordinates('A'.$r);
            $drawing->setOffsetX($offsetX);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($sheet);
            $sheet->getRowDimension($r)->setRowHeight(120);
            $r++;
        } elseif ($tg && $tg->fotos->isNotEmpty()) {
            $sheet->setCellValue('A'.$r, 'File foto tidak ditemukan');
            $r++;
        }

        $r += 1;
        self::ttdBlock($sheet, $r, $penandatangan, 'D');
    }

    private static function horizontal(Worksheet $sheet, array $area, string $periodeLabel, $penandatangan): void
    {
        $titikRows = $area['rows']->filter(fn ($row) => $row['tagihan'])->values();
        $n = max(1, $titikRows->count());

        $titikCols = [];
        for ($i = 0; $i < $n; $i++) {
            $titikCols[] = Coordinate::stringFromColumnIndex(3 + $i);
        }
        $jumlahCol = Coordinate::stringFromColumnIndex(3 + $n);
        $unitCol = Coordinate::stringFromColumnIndex(4 + $n);
        $lastCol = $unitCol;

        $sheet->getColumnDimension('A')->setWidth(22);
        $sheet->getColumnDimension('B')->setWidth(13);
        foreach ($titikCols as $col) {
            $sheet->getColumnDimension($col)->setWidth(13);
        }
        $sheet->getColumnDimension($jumlahCol)->setWidth(14);
        $sheet->getColumnDimension($unitCol)->setWidth(8);

        $r = self::headerBlock($sheet, $periodeLabel, $lastCol, 'Rekap Perhitungan Pemakaian Air Baku');
        $dataStart = $r;

        self::titleRow($sheet, $r, 'PENGAMBIL / PEMAKAI', $lastCol);
        self::kv($sheet, $r, 'NAMA', $area['area']->nama, null, true, null, $lastCol);
        self::kv($sheet, $r, 'ALAMAT', $area['area']->alamat ?: '-', null, false, null, $lastCol);
        self::kv($sheet, $r, 'LOKASI FLOW METER', $area['area']->nama, null, false, null, $lastCol);
        self::titleRow($sheet, $r, 'PERHITUNGAN PEMAKAIAN', $lastCol);

        $sheet->setCellValue('A'.$r, '');
        foreach ($titikRows as $i => $row) {
            self::hcell($sheet, $titikCols[$i].$r, $row['titik_meter']->nama, true);
        }
        self::hcell($sheet, $jumlahCol.$r, 'Jumlah', true);
        $r++;

        $ini = $lalu = $delta = $faktor = $pemakaian = $tarif = $subtotalTitik = [];
        foreach ($titikRows as $i => $row) {
            $tg = $row['tagihan'];
            $ini[$i] = (int) round((float) $tg->meter_ini);
            $lalu[$i] = (int) round((float) $tg->meter_lalu);
            $delta[$i] = $ini[$i] - $lalu[$i];
            $faktor[$i] = (float) $tg->meter_faktor;
            $pemakaian[$i] = (int) round((float) $tg->pemakaian);
            $tarif[$i] = (float) $tg->tarif;
            $subtotalTitik[$i] = (float) $tg->jumlah;
        }

        self::pivotRow($sheet, $r, 'Bulan ini', '( a )', $titikCols, $ini, $jumlahCol, null, $unitCol, 'M³');
        self::pivotRow($sheet, $r, 'Bulan lalu', '( b )', $titikCols, $lalu, $jumlahCol, null, $unitCol, 'M³');
        self::pivotRow($sheet, $r, 'Jumlah Pengambilan', '( c = a - b )', $titikCols, $delta, $jumlahCol, null, $unitCol, 'M³');
        self::pivotRow($sheet, $r, 'Meter Faktor', '( d )', $titikCols, array_map(fn ($v) => number_format($v, 0, ',', '.'), $faktor), $jumlahCol, null, $unitCol, null);
        self::pivotRow($sheet, $r, 'Jumlah Pengambilan', '( e = c x d )', $titikCols, $pemakaian, $jumlahCol, null, $unitCol, 'M³');
        self::pivotRow($sheet, $r, 'Tarif / Harga', 'Rp', $titikCols, $tarif, $jumlahCol, null, $unitCol, '/M³', false, null, null, '#,##0');

        $grandTotal = array_sum($subtotalTitik);
        self::pivotRow(
            $sheet,
            $r,
            'Jumlah',
            'Rp',
            $titikCols,
            $subtotalTitik,
            $jumlahCol,
            $grandTotal,
            $unitCol,
            null,
            true,
            null,
            null,
            '#,##0;(#,##0)'
        );

        if ($area['kena_ppn']) {
            self::pivotRow($sheet, $r, 'PPN '.number_format($area['persen_ppn'], 0, ',', '.').'%', null, $titikCols, [], $jumlahCol, (float) $area['ppn'], $unitCol, null, false, self::PPN_FILL, $lastCol, '#,##0');
            self::pivotRow($sheet, $r, 'Total', null, $titikCols, [], $jumlahCol, (float) $area['total'], $unitCol, null, true, self::GRAND_FILL, $lastCol, '#,##0');
        }

        self::borders($sheet, 'A'.$dataStart.':'.$lastCol.($r - 1));
        $r++;

        $photoRow = $r;
        $drawings = [];
        $missingFoto = false;

        foreach ($titikRows as $i => $row) {
            $tg = $row['tagihan'];
            $fotoPath = self::resolveFotoPath($tg);

            if ($fotoPath) {
                $drawing = new Drawing;
                $drawing->setName('foto-'.$i);
                $drawing->setPath($fotoPath);
                $drawing->setResizeProportional(true);
                $drawing->setHeight(100);
                $drawings[$i] = $drawing;

                $neededWidthPx = $drawing->getWidth() + 10;
                $currentWidthPx = $sheet->getColumnDimension($titikCols[$i])->getWidth() * 7;
                if ($neededWidthPx > $currentWidthPx) {
                    $newWidthUnit = min(self::MAX_TITIK_COL_WIDTH, $neededWidthPx / 7);
                    $sheet->getColumnDimension($titikCols[$i])->setWidth($newWidthUnit);
                }
            } elseif ($tg && $tg->fotos->isNotEmpty()) {
                $missingFoto = true;
            }
        }

        $anyFoto = ! empty($drawings);
        if ($anyFoto) {
            foreach ($drawings as $i => $drawing) {
                $colWidthPx = $sheet->getColumnDimension($titikCols[$i])->getWidth() * 7;
                $offsetX = max(2, (int) (($colWidthPx - $drawing->getWidth()) / 2));

                $drawing->setCoordinates($titikCols[$i].$photoRow);
                $drawing->setOffsetX($offsetX);
                $drawing->setOffsetY(5);
                $drawing->setWorksheet($sheet);
            }
            $sheet->getRowDimension($photoRow)->setRowHeight(110);
            $r = $photoRow + 1;
        } elseif ($missingFoto) {
            $sheet->setCellValue('A'.$photoRow, 'File foto tidak ditemukan');
            $r = $photoRow + 1;
        }

        $r += 2;
        self::ttdBlock($sheet, $r, $penandatangan, $lastCol);
    }

    private static function list(Worksheet $sheet, array $area, string $periodeLabel, $penandatangan): void
    {
        $rows = $area['rows']
            ->filter(fn ($row) => ($row['titik_meter']->status ?? 'aktif') === 'aktif')
            ->values();

        $lastCol = 'G';
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(26);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(13);
        $sheet->getColumnDimension('G')->setWidth(16);

        $r = self::headerBlock($sheet, $periodeLabel, $lastCol, 'BIAYA PEMAKAIAN AIR');
        $dataStart = $r;

        $headRow1 = $r;
        $headRow2 = $r + 1;

        $sheet->mergeCells('A'.$headRow1.':A'.$headRow2);
        $sheet->setCellValue('A'.$headRow1, 'No');

        $sheet->mergeCells('B'.$headRow1.':B'.$headRow2);
        $sheet->setCellValue('B'.$headRow1, 'Nama Titik Meter');

        $sheet->mergeCells('C'.$headRow1.':D'.$headRow1);
        $sheet->setCellValue('C'.$headRow1, 'COUNTER M3');
        $sheet->setCellValue('C'.$headRow2, 'Bulan Ini');
        $sheet->setCellValue('D'.$headRow2, 'Bulan Lalu');

        $sheet->mergeCells('E'.$headRow1.':E'.$headRow2);
        $sheet->setCellValue('E'.$headRow1, 'Jumlah Pengambilan');

        $sheet->mergeCells('F'.$headRow1.':F'.$headRow2);
        $sheet->setCellValue('F'.$headRow1, 'Tarif Rp/M3');

        $sheet->mergeCells('G'.$headRow1.':G'.$headRow2);
        $sheet->setCellValue('G'.$headRow1, 'Jumlah (Rp)');

        foreach (range('A', $lastCol) as $col) {
            $range = $col.$headRow1.':'.$col.$headRow2;
            $sheet->getStyle($range)->getFont()->setName('Calibri')->setBold(true);
            $sheet->getStyle($range)->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
            $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::HEADER_FILL);
        }
        $sheet->getRowDimension($headRow2)->setRowHeight(16);
        $r = $headRow2 + 1;

        $no = 0;
        foreach ($rows as $row) {
            $tg = $row['tagihan'] ?? null;
            $no++;

            $sheet->setCellValue('A'.$r, $no);
            $sheet->setCellValue('B'.$r, $row['titik_meter']->nama);
            $sheet->setCellValue('C'.$r, $tg ? (int) round((float) $tg->meter_ini) : null);
            $sheet->setCellValue('D'.$r, $tg ? (int) round((float) $tg->meter_lalu) : null);
            $sheet->setCellValue('E'.$r, $tg ? (int) round((float) $tg->pemakaian) : null);
            $sheet->setCellValue('F'.$r, $tg ? (float) $tg->tarif : (float) ($row['titik_meter']->tarif_harga ?? 0));
            $sheet->setCellValue('G'.$r, $tg ? (float) $tg->jumlah : null);

            foreach (range('A', $lastCol) as $col) {
                $sheet->getStyle($col.$r)->getFont()->setName('Calibri');
            }
            $sheet->getStyle('A'.$r)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('C'.$r.':E'.$r)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('F'.$r)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('F'.$r)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('G'.$r)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('G'.$r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('G'.$r)->getFont()->setBold(true);

            $r++;
        }

        $sheet->mergeCells('A'.$r.':E'.$r);
        $sheet->setCellValue('A'.$r, 'Subtotal');
        $sheet->getStyle('A'.$r)->getFont()->setName('Calibri')->setBold(true);
        $sheet->setCellValue('G'.$r, (float) $area['subtotal']);
        $sheet->getStyle('G'.$r)->getFont()->setName('Calibri')->setBold(true);
        $sheet->getStyle('G'.$r)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('G'.$r)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A'.$r.':'.$lastCol.$r)
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::GRAND_FILL);
        $r++;

        if ($area['kena_ppn']) {
            $sheet->mergeCells('A'.$r.':E'.$r);
            $sheet->setCellValue('A'.$r, 'PPN '.number_format($area['persen_ppn'], 0, ',', '.').'%');
            $sheet->getStyle('A'.$r)->getFont()->setName('Calibri');
            $sheet->setCellValue('G'.$r, (float) $area['ppn']);
            $sheet->getStyle('G'.$r)->getFont()->setName('Calibri');
            $sheet->getStyle('G'.$r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('G'.$r)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A'.$r.':'.$lastCol.$r)
                ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::PPN_FILL);
            $r++;

            $sheet->mergeCells('A'.$r.':E'.$r);
            $sheet->setCellValue('A'.$r, 'Total');
            $sheet->getStyle('A'.$r)->getFont()->setName('Calibri')->setBold(true);
            $sheet->setCellValue('G'.$r, (float) $area['total']);
            $sheet->getStyle('G'.$r)->getFont()->setName('Calibri')->setBold(true);
            $sheet->getStyle('G'.$r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('G'.$r)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A'.$r.':'.$lastCol.$r)
                ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::GRAND_FILL);
            $r++;
        }

        self::borders($sheet, 'A'.$dataStart.':'.$lastCol.($r - 1));
        $r++;

        $barisFoto = $rows->filter(fn ($row) => $row['tagihan'])->values();
        $anyFoto = $barisFoto->contains(fn ($row) => self::resolveFotoPath($row['tagihan']) !== null);

        if ($barisFoto->isNotEmpty() && $anyFoto) {
            $sheet->setCellValue('A'.$r, 'Foto Meter :');
            $sheet->getStyle('A'.$r)->getFont()->setName('Calibri')->setBold(true);
            $r += 2;

            $fotoSlots = self::fotoGridColumns($lastCol, 4);

            foreach ($barisFoto->chunk(4) as $chunk) {
                $chunkArr = $chunk->values();
                $labelRow = $r;
                $photoRow = $r + 1;

                foreach ($chunkArr as $i => $row) {
                    $slot = $fotoSlots[$i];
                    [$startCol, $endCol] = [reset($slot), end($slot)];

                    $sheet->mergeCells($startCol.$labelRow.':'.$endCol.$labelRow);
                    $sheet->setCellValue($startCol.$labelRow, $row['titik_meter']->nama);
                    $sheet->getStyle($startCol.$labelRow)->getFont()->setName('Calibri')->setBold(true);
                    $sheet->getStyle($startCol.$labelRow)->getAlignment()->setHorizontal('center');
                    $sheet->getStyle($startCol.$labelRow.':'.$endCol.$labelRow)
                        ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::HEADER_FILL);

                    $sheet->mergeCells($startCol.$photoRow.':'.$endCol.$photoRow);

                    $fotoPath = self::resolveFotoPath($row['tagihan']);
                    if ($fotoPath) {
                        $drawing = new Drawing;
                        $drawing->setName('foto-list-'.$labelRow.'-'.$i);
                        $drawing->setPath($fotoPath);
                        $drawing->setResizeProportional(true);
                        $drawing->setHeight(100);

                        $slotWidthPx = array_sum(array_map(
                            fn ($col) => $sheet->getColumnDimension($col)->getWidth() * 7,
                            $slot
                        ));

                        if ($drawing->getWidth() + 10 > $slotWidthPx) {
                            $drawing->setWidth((int) floor($slotWidthPx - 10));
                        }

                        $offsetX = max(2, (int) (($slotWidthPx - $drawing->getWidth()) / 2));

                        $drawing->setCoordinates($startCol.$photoRow);
                        $drawing->setOffsetX($offsetX);
                        $drawing->setOffsetY(5);
                        $drawing->setWorksheet($sheet);
                    } else {
                        $sheet->setCellValue($startCol.$photoRow, 'file tidak ditemukan');
                        $sheet->getStyle($startCol.$photoRow)->getFont()->setName('Calibri')->setItalic(true);
                        $sheet->getStyle($startCol.$photoRow)->getAlignment()->setHorizontal('center')->setVertical('center');
                    }
                }

                $sheet->getRowDimension($photoRow)->setRowHeight(105);
                $r = $photoRow + 1;
            }
        }

        $r += 1;
        self::ttdBlock($sheet, $r, $penandatangan, $lastCol);
    }

    private static function fotoGridColumns(string $lastCol, int $n = 4): array
    {
        $cols = range('A', $lastCol);
        $groupSize = (int) max(1, ceil(count($cols) / $n));
        $chunks = array_values(array_chunk($cols, $groupSize));

        while (count($chunks) < $n) {
            $chunks[] = [end($cols)];
        }

        return array_slice($chunks, 0, $n);
    }

    private static function titleRow(Worksheet $sheet, int &$r, string $label, string $lastCol = 'D'): void
    {
        $sheet->setCellValue('A'.$r, $label);
        $sheet->mergeCells('A'.$r.':'.$lastCol.$r);
        $sheet->getStyle('A'.$r)->getFont()->setName('Calibri')->setBold(true);
        $r++;
    }

    private static function kv(Worksheet $sheet, int &$r, string $label, mixed $value, ?string $kode = null, bool $bold = false, ?string $satuan = null, string $lastCol = 'D', ?string $numFmt = null): void
    {
        if ($kode !== null) {
            $sheet->setCellValue('A'.$r, $label);
            $sheet->getStyle('A'.$r)->getFont()->setName('Calibri');

            $sheet->setCellValue('B'.$r, $kode);
            $sheet->getStyle('B'.$r)->getFont()->setName('Calibri');
            $sheet->getStyle('B'.$r)->getAlignment()->setHorizontal('center');
        } else {
            $sheet->mergeCells('A'.$r.':B'.$r);
            $sheet->setCellValue('A'.$r, $label);
            $sheet->getStyle('A'.$r)->getFont()->setName('Calibri');
            $sheet->getStyle('A'.$r)->getAlignment()->setHorizontal('left');
        }

        if ($satuan !== null) {
            $sheet->setCellValue('C'.$r, $value);
            $sheet->getStyle('C'.$r)->getFont()->setName('Calibri');
            $sheet->getStyle('C'.$r)->getAlignment()->setHorizontal('center');

            $sheet->setCellValue('D'.$r, $satuan);
            $sheet->getStyle('D'.$r)->getFont()->setName('Calibri');
            $sheet->getStyle('D'.$r)->getAlignment()->setHorizontal('center');
        } else {
            $sheet->mergeCells('C'.$r.':'.$lastCol.$r);
            $sheet->setCellValue('C'.$r, $value);
            $sheet->getStyle('C'.$r)->getFont()->setName('Calibri');
            $sheet->getStyle('C'.$r)->getAlignment()->setHorizontal('center');
        }

        if ($bold) {
            $sheet->getStyle('A'.$r)->getFont()->setBold(true);
            $sheet->getStyle('C'.$r)->getFont()->setBold(true);
        }

        if ($numFmt !== null) {
            $sheet->getStyle('C'.$r)->getNumberFormat()->setFormatCode($numFmt);
        }
        $r++;
    }

    private static function pivotRow(
        Worksheet $sheet,
        int &$r,
        string $label,
        ?string $kode,
        array $titikCols,
        array $values,
        string $jumlahCol,
        mixed $jumlahValue,
        string $unitCol,
        ?string $satuan,
        bool $bold = false,
        ?string $fillColor = null,
        ?string $lastCol = null,
        ?string $numFmt = null
    ): void {
        $sheet->setCellValue('A'.$r, $label);
        $sheet->getStyle('A'.$r)->getFont()->setName('Calibri');

        if ($kode !== null) {
            $sheet->setCellValue('B'.$r, $kode);
            $sheet->getStyle('B'.$r)->getFont()->setName('Calibri');
            $sheet->getStyle('B'.$r)->getAlignment()->setHorizontal('center');
        }

        foreach ($titikCols as $i => $col) {
            if (array_key_exists($i, $values)) {
                $sheet->setCellValue($col.$r, $values[$i]);
                $sheet->getStyle($col.$r)->getFont()->setName('Calibri');
                $sheet->getStyle($col.$r)->getAlignment()->setHorizontal('center');
                if ($numFmt !== null) {
                    $sheet->getStyle($col.$r)->getNumberFormat()->setFormatCode($numFmt);
                }
            }
        }

        if ($jumlahValue !== null) {
            $sheet->setCellValue($jumlahCol.$r, $jumlahValue);
            $sheet->getStyle($jumlahCol.$r)->getFont()->setName('Calibri');
            $sheet->getStyle($jumlahCol.$r)->getAlignment()->setHorizontal('center');
            if ($numFmt !== null) {
                $sheet->getStyle($jumlahCol.$r)->getNumberFormat()->setFormatCode($numFmt);
            }
        }

        if ($satuan !== null) {
            $sheet->setCellValue($unitCol.$r, $satuan);
            $sheet->getStyle($unitCol.$r)->getFont()->setName('Calibri');
            $sheet->getStyle($unitCol.$r)->getAlignment()->setHorizontal('center');
        }

        if ($bold) {
            $sheet->getStyle('A'.$r)->getFont()->setBold(true);
            $sheet->getStyle('B'.$r)->getFont()->setBold(true);
            foreach ($titikCols as $col) {
                $sheet->getStyle($col.$r)->getFont()->setBold(true);
            }
            $sheet->getStyle($jumlahCol.$r)->getFont()->setBold(true);
        }

        if ($fillColor !== null && $lastCol !== null) {
            $sheet->getStyle('A'.$r.':'.$lastCol.$r)
                ->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB($fillColor);
        }

        $r++;
    }

    private static function hcell(Worksheet $sheet, string $cell, string $value, bool $bold): void
    {
        $sheet->setCellValue($cell, $value);
        $style = $sheet->getStyle($cell);
        $style->getFont()->setName('Calibri')->setBold($bold);
        $style->getAlignment()->setHorizontal('center');
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::HEADER_FILL);
    }

    private static function borders(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getBorders()->applyFromArray([
            'allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF555555']],
        ]);
    }

    private static function resolveFotoPath($tg): ?string
    {
        if (! $tg || ! $tg->fotos || $tg->fotos->isEmpty()) {
            return null;
        }

        $rawPath = $tg->fotos->first()->path_foto;

        $path = str_starts_with($rawPath, 'uploads/')
            ? public_path($rawPath)
            : storage_path('app/public/'.$rawPath);

        return is_file($path) ? $path : null;
    }

    /**
     * Blok tanda tangan — disamakan tampilannya dengan layout target:
     * baris 1 judul "Mengetahui / Menyetujui" (kiri) SEJAJAR dengan tempat &
     * tanggal (kanan) di baris yang sama, baris 2 jabatan per kolom persis
     * di bawahnya, lalu baris spasi tanda tangan, baru baris nama per kolom.
     */
    private static function ttdBlock(Worksheet $sheet, int &$r, $penandatangan, string $lastCol = 'D'): void
    {
        if (! $penandatangan || $penandatangan->isEmpty()) {
            return;
        }

        $cols = range('A', $lastCol);
        $n = $penandatangan->count();

        $colWidths = [];
        foreach ($cols as $col) {
            $colWidths[$col] = max(1, $sheet->getColumnDimension($col)->getWidth());
        }
        $totalWidth = array_sum($colWidths);

        $chunks = [];
        $current = [];
        $currentWidth = 0;
        $colsLeft = count($cols);
        $groupsLeft = $n;

        foreach ($cols as $col) {
            $current[] = $col;
            $currentWidth += $colWidths[$col];
            $colsLeft--;

            $targetWidth = $totalWidth * (count($chunks) + 1) / $n;
            $shouldBreak = $groupsLeft > 1
                && $currentWidth >= $targetWidth
                && $colsLeft >= ($groupsLeft - 1);

            if ($shouldBreak) {
                $chunks[] = $current;
                $current = [];
                $currentWidth = 0;
                $groupsLeft--;
            }
        }
        if (! empty($current)) {
            $chunks[] = $current;
        }
        while (count($chunks) < $n) {
            $chunks[] = [end($cols)];
        }

        $lastChunk = end($chunks);

        $first = $penandatangan->first();
        $tempat = $first->tempat ?? '';
        $tanggal = Carbon::now()->locale('id')->translatedFormat('d F Y');

        // Baris 1: judul "Mengetahui / Menyetujui" di blok tanda tangan
        // PERTAMA (kiri), tempat & tanggal di blok tanda tangan TERAKHIR
        // (kanan) — sejajar di baris yang sama, sesuai layout target.
        // Kalau cuma ada 1 penandatangan, judul & tanggal digabung jadi
        // satu baris full-width (nggak ada blok kedua buat taruh tanggal).
        $titleRow = $r;

        if ($n === 1) {
            $sheet->mergeCells('A'.$titleRow.':'.$lastCol.$titleRow);
            $sheet->setCellValue('A'.$titleRow, 'Mengetahui / Menyetujui — '.($tempat ? $tempat.', ' : '').$tanggal);
            $sheet->getStyle('A'.$titleRow)->getFont()->setName('Calibri')->setBold(true);
            $sheet->getStyle('A'.$titleRow)->getAlignment()->setHorizontal('center');
        } else {
            $titleChunk = $chunks[0];
            [$titleStart, $titleEnd] = [reset($titleChunk), end($titleChunk)];
            $sheet->mergeCells($titleStart.$titleRow.':'.$titleEnd.$titleRow);
            $sheet->setCellValue($titleStart.$titleRow, 'Mengetahui / Menyetujui');
            $sheet->getStyle($titleStart.$titleRow)->getFont()->setName('Calibri')->setBold(true);
            $sheet->getStyle($titleStart.$titleRow)->getAlignment()->setHorizontal('center');

            [$dateStart, $dateEnd] = [reset($lastChunk), end($lastChunk)];
            $sheet->mergeCells($dateStart.$titleRow.':'.$dateEnd.$titleRow);
            $sheet->setCellValue($dateStart.$titleRow, ($tempat ? $tempat.', ' : '').$tanggal);
            $sheet->getStyle($dateStart.$titleRow)->getFont()->setName('Calibri');
            $sheet->getStyle($dateStart.$titleRow)->getAlignment()->setHorizontal('center');
        }
        $r++;

        $jabatanRow = $r;
        $spaceRow = $r + 1;
        $namaRow = $r + 2;

        foreach ($penandatangan as $i => $p) {
            $chunk = $chunks[$i] ?? $lastChunk;
            [$startCol, $endCol] = [reset($chunk), end($chunk)];

            $sheet->mergeCells($startCol.$jabatanRow.':'.$endCol.$jabatanRow);
            $sheet->setCellValue($startCol.$jabatanRow, $p->jabatan);
            $sheet->getStyle($startCol.$jabatanRow)->getFont()->setName('Calibri')->setBold(true);
            $sheet->getStyle($startCol.$jabatanRow)->getAlignment()->setHorizontal('center');

            $sheet->mergeCells($startCol.$namaRow.':'.$endCol.$namaRow);
            $sheet->setCellValue($startCol.$namaRow, $p->nama ?: '...................................');
            $sheet->getStyle($startCol.$namaRow)->getFont()->setName('Calibri')->setBold(true);
            $sheet->getStyle($startCol.$namaRow)->getAlignment()->setHorizontal('center');
        }
        $sheet->getRowDimension($spaceRow)->setRowHeight(45);

        $r = $namaRow + 2;
    }

    private static function rp(mixed $value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }

    private static function rp2(mixed $value): string
    {
        return 'Rp '.number_format((float) $value, 2, ',', '.');
    }

    private static function fmt0(mixed $value): string
    {
        return number_format((float) $value, 0, ',', '.');
    }

    private static function fmt2(mixed $value): string
    {
        return number_format((float) $value, 2, ',', '.');
    }

    private static function fmtAccounting(mixed $value): string
    {
        $v = (float) $value;
        if ($v == 0) {
            return '–';
        }

        return $v < 0
            ? '('.number_format(abs($v), 0, ',', '.').')'
            : number_format($v, 0, ',', '.');
    }
}