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
        if (($area['jml_titik'] ?? $area['rows']->count()) === 1) {
            self::vertical($sheet, $area, $periodeLabel, $penandatangan);
        } else {
            self::horizontal($sheet, $area, $periodeLabel, $penandatangan);
        }
    }

    /**
     * Header dokumen: logo + nama perusahaan, lalu judul dokumen + periode.
     * $title dibedakan per jenis layout ("BIAYA PEMAKAIAN AIR" utk single titik,
     * "Rekap Perhitungan Pemakaian Air Baku" utk multi titik).
     */
    private static function headerBlock(Worksheet $sheet, string $periodeLabel, string $lastCol, string $title = 'BIAYA PEMAKAIAN AIR'): int
    {
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);

        // Logo dibuat pas & rapi, cuma nutup baris 1-2 (jangan sampai numpuk ke tabel)
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
            $drawing->setHeight(35); // logo nutup kolom A-B, tinggi tetep pas di 2 baris header
            $drawing->setWorksheet($sheet);
        }

        // Logo makan kolom A-B, nama perusahaan mulai dari kolom C dan CUMA
        // di-merge C:D (bukan sampai lastCol) — kolom sisanya di kanan tetap
        // kolom biasa/kosong, jangan ikut ke-stretch.
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

        $colWidthsPx = ['A' => 22, 'B' => 13, 'C' => 16, 'D' => 12]; // dipakai buat estimasi lebar tabel (px = unit * 7)
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

        // Border cuma buat tabel angka, foto ditaruh polos di bawahnya tanpa dikotakin
        self::borders($sheet, 'A'.$dataStart.':D'.($r - 1));
        $r++;

        $fotoPath = self::resolveFotoPath($tg);
        if ($fotoPath) {
            $drawing = new Drawing;
            $drawing->setName('foto-meter');
            $drawing->setPath($fotoPath);
            $drawing->setResizeProportional(true);
            $drawing->setHeight(110);

            // Foto ditaruh di tengah lebar tabel (kolom A-D), bukan mepet kiri
            $tableWidthPx = array_sum($colWidthsPx) * 7;
            $offsetX = max(5, (int) (($tableWidthPx - $drawing->getWidth()) / 2));

            $drawing->setCoordinates('A'.$r);
            $drawing->setOffsetX($offsetX);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($sheet);
            $sheet->getRowDimension($r)->setRowHeight(120);
            $r++;
        } elseif ($tg && $tg->fotos->isNotEmpty()) {
            // ada record foto tapi file fisiknya nggak ketemu di storage
            $sheet->setCellValue('A'.$r, 'File foto tidak ditemukan');
            $r++;
        }

        $r += 1;
        self::ttdBlock($sheet, $r, $penandatangan, 'D');
    }

    /**
     * Layout multi-titik: tabel "pivot" — baris = langkah perhitungan
     * (Bulan ini, Bulan lalu, dst), kolom = tiap titik meter + kolom
     * "Jumlah" (total gabungan) di ujung kanan, dan kolom satuan paling kanan.
     */
    private static function horizontal(Worksheet $sheet, array $area, string $periodeLabel, $penandatangan): void
    {
        $titikRows = $area['rows']->filter(fn ($row) => $row['tagihan'])->values();
        $n = max(1, $titikRows->count());

        // Kolom: A = label, B = kode rumus, C..(C+n-1) = tiap titik meter,
        // kolom berikutnya = Jumlah (total), kolom terakhir = satuan.
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

        // Header kolom per titik meter
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

        // Foto meter per titik, ditaruh sejajar di bawah kolom masing-masing titik
        // (kayak di layout "standar"/vertical), bukan cuma satu foto gabungan.
        $photoRow = $r;
        $anyFoto = false;
        $missingFoto = false;
        foreach ($titikRows as $i => $row) {
            $tg = $row['tagihan'];
            $fotoPath = self::resolveFotoPath($tg);
            if ($fotoPath) {
                $anyFoto = true;
                $drawing = new Drawing;
                $drawing->setName('foto-'.$i);
                $drawing->setPath($fotoPath);
                $drawing->setResizeProportional(true);
                $drawing->setHeight(100);

                // Posisikan foto di tengah lebar kolom titik meter yang bersangkutan
                $colWidthPx = $sheet->getColumnDimension($titikCols[$i])->getWidth() * 7;
                $offsetX = max(2, (int) (($colWidthPx - $drawing->getWidth()) / 2));

                $drawing->setCoordinates($titikCols[$i].$photoRow);
                $drawing->setOffsetX($offsetX);
                $drawing->setOffsetY(5);
                $drawing->setWorksheet($sheet);
            } elseif ($tg && $tg->fotos->isNotEmpty()) {
                $missingFoto = true;
            }
        }

        if ($anyFoto) {
            $sheet->getRowDimension($photoRow)->setRowHeight(110);
            $r = $photoRow + 1;
        } elseif ($missingFoto) {
            $sheet->setCellValue('A'.$photoRow, 'File foto tidak ditemukan');
            $r = $photoRow + 1;
        }

        $r += 2;
        self::ttdBlock($sheet, $r, $penandatangan, $lastCol);
    }

    private static function titleRow(Worksheet $sheet, int &$r, string $label, string $lastCol = 'D'): void
    {
        $sheet->setCellValue('A'.$r, $label);
        $sheet->mergeCells('A'.$r.':'.$lastCol.$r);
        $sheet->getStyle('A'.$r)->getFont()->setName('Calibri')->setBold(true);
        $r++;
    }

    /**
     * Baris label:value. $kode (opsional) diisi ke kolom B, misalnya notasi
     * rumus "( a )", "( c = a - b )", atau prefiks satuan seperti "Rp".
     * Kalau $satuan diisi, value ditaruh sendiri di kolom C dan satuan di
     * kolom D. Kalau tidak, value di-merge dari kolom C sampai $lastCol.
     *
     * Kalau $kode kosong, label di-merge A:B biar nggak ada kolom B yang
     * nganggur / bikin jarak kosong antara label dan value.
     */
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

    /**
     * Baris tabel pivot: satu label + kode rumus, lalu satu value per
     * titik meter (di $titikCols), kolom Jumlah (opsional), dan satuan (opsional).
     */
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

    /**
     * Ambil path file foto meter yang valid dari relasi TagihanAir::fotos().
     * Menangani 2 skema penyimpanan: prefix "uploads/" (legacy, langsung di public/)
     * dan skema baru lewat Storage::disk('public').
     */
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
     * Blok tanda tangan. Tanggal & tempat ditaruh SEJAJAR (satu baris) dengan
     * judul "Mengetahui/Menyetujui ;", rata kiri, dan diposisikan di atas
     * kolom penandatangan paling kanan (biasanya Asman) — sesuai contoh asli.
     */
    private static function ttdBlock(Worksheet $sheet, int &$r, $penandatangan, string $lastCol = 'D'): void
    {
        if (! $penandatangan || $penandatangan->isEmpty()) {
            return;
        }

        $cols = range('A', $lastCol);
        $chunks = array_chunk($cols, (int) max(1, ceil(count($cols) / $penandatangan->count())));
        $lastChunk = end($chunks);

        // Kolom buat tanggal = grup kolom penandatangan terakhir.
        // Kolom buat judul "Mengetahui/Menyetujui ;" = sisa kolom sebelum itu
        // (kalau cuma ada 1 grup kolom total, bagi dua grup itu sendiri).
        $titleCols = array_diff($cols, $lastChunk);
        if (empty($titleCols)) {
            $half = (int) ceil(count($lastChunk) / 2);
            $titleCols = array_slice($lastChunk, 0, $half);
            $dateCols = array_slice($lastChunk, $half) ?: $lastChunk;
        } else {
            $dateCols = $lastChunk;
        }

        $first = $penandatangan->first();
        $tempat = $first->tempat ?? '';
        $tanggal = Carbon::now()->locale('id')->translatedFormat('d F Y');

        $titleRow = $r;
        $sheet->mergeCells(reset($titleCols).$titleRow.':'.end($titleCols).$titleRow);
        $sheet->setCellValue(reset($titleCols).$titleRow, 'Mengetahui/Menyetujui ;');
        $sheet->getStyle(reset($titleCols).$titleRow)->getFont()->setName('Calibri')->setBold(true);
        $sheet->getStyle(reset($titleCols).$titleRow)->getAlignment()->setHorizontal('center');

        $sheet->mergeCells(reset($dateCols).$titleRow.':'.end($dateCols).$titleRow);
        $sheet->setCellValue(reset($dateCols).$titleRow, ($tempat ? $tempat.', ' : '').$tanggal);
        $sheet->getStyle(reset($dateCols).$titleRow)->getFont()->setName('Calibri');
        $sheet->getStyle(reset($dateCols).$titleRow)->getAlignment()->setHorizontal('center');
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

    /**
     * Format akuntansi: 0 -> "–", negatif -> "(1.234)", positif -> "1.234".
     * Dipakai buat baris "Jumlah (Rp)" per titik di tabel pivot multi-titik.
     */
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