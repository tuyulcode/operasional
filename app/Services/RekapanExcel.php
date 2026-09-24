<?php

namespace App\Services;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapanExcel
{
    private const HEADER_FILL = 'FFD9E2F3';   // biru muda untuk header tabel

    private const TOTAL_FILL = 'FFE2EFDA';    // hijau muda untuk Jumlah Total / Total

    private const PPN_FILL = 'FFFFF2CC';      // kuning muda untuk baris PPN

    private const NUM_FMT = '#,##0;-#,##0;"-"';

    private const LAST_COL = 'G';             // A..G = 7 kolom

    private const JUMLAH_COL = 'G';           // nilai rupiah

    public static function generate(array $report): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        $penandatangan = $report['penandatangan'] ?? collect();
        $periodeLabel = $report['periodeLabel'] ?? '';

        $used = [];
        foreach ($report['data'] as $area) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle(self::sheetTitle($area['area']->nama, $used));
            self::list($sheet, $area, $periodeLabel, $penandatangan);
        }

        if ($spreadsheet->getSheetCount() === 0) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('Rekap');
            self::headerBlock($sheet, '', '-', null);
            $sheet->setCellValue('A8', 'Tidak ada data untuk periode ini.');
        }

        $spreadsheet->setActiveSheetIndex(0);

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

    private static function headerBlock(Worksheet $sheet, string $periodeLabel, string $namaArea, ?string $alamatArea): int
    {
        $lastCol = self::LAST_COL;
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
            $drawing->setHeight(32);
            $drawing->setWorksheet($sheet);
        }

        $sheet->mergeCells('C1:E1');
        $sheet->setCellValue('C1', 'PT PLN NUSANTARA POWER');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('C1')->getAlignment()->setVertical('center');

        $sheet->mergeCells('C2:E2');
        $sheet->setCellValue('C2', 'UNIT PEMBANGKITAN PAITON');
        $sheet->getStyle('C2')->getFont()->setSize(10);
        $sheet->getStyle('C2')->getAlignment()->setVertical('center');

        $r = 3;

        $judul = [['Rekap Biaya Pemakaian Air', 14, true, 26]];
        $judul[] = ['Nama Pengguna : '.$namaArea, 11, true, 16];
        if ($alamatArea) {
            $judul[] = ['Lokasi Flow Meter : '.$alamatArea, 11, true, 16];
        }
        $judul[] = ['Bulan : '.$periodeLabel, 11, true, 18];

        foreach ($judul as [$teks, $size, $bold, $height]) {
            $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
            $sheet->setCellValue("A{$r}", $teks);
            $sheet->getStyle("A{$r}")->getFont()->setBold($bold)->setSize($size);
            $sheet->getStyle("A{$r}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getRowDimension($r)->setRowHeight($height);
            $r++;
        }

        $sheet->getRowDimension($r)->setRowHeight(8);
        $r++;

        return $r;
    }

    /**
     * Satu-satunya layout rekapan: tabel list (mengikuti sheet PUNCAK 1),
     * dipakai untuk berapa pun jumlah titik meternya.
     */
    private static function list(Worksheet $sheet, array $area, string $periodeLabel, $penandatangan): void
    {
        $lastCol = self::LAST_COL;

        $rows = $area['rows']
            ->filter(fn ($row) => ($row['titik_meter']->status ?? 'aktif') === 'aktif')
            ->values();

        // Lebar diatur supaya (A+B) = (C+D) = (E+F) = G = 26,
        // sehingga saat dibagi jadi slot foto (2 atau 4 foto/baris),
        // hasilnya presisi sama rata seperti tata letak di PDF.
        $sheet->getColumnDimension('A')->setWidth(7);   // No. Urut
        $sheet->getColumnDimension('B')->setWidth(19);  // Nama Titik Meter
        $sheet->getColumnDimension('C')->setWidth(12);  // Bulan Ini
        $sheet->getColumnDimension('D')->setWidth(14);  // Bulan Lalu
        $sheet->getColumnDimension('E')->setWidth(12);  // Jumlah Pengambilan
        $sheet->getColumnDimension('F')->setWidth(14);  // Tarif
        $sheet->getColumnDimension('G')->setWidth(26);  // Jumlah Rp

        $r = self::headerBlock($sheet, $periodeLabel, $area['area']->nama, $area['area']->alamat ?: null);
        $dataStart = $r;

        $headRow1 = $r;
        $headRow2 = $r + 1;

        $sheet->mergeCells('A'.$headRow1.':A'.$headRow2);
        $sheet->setCellValue('A'.$headRow1, "No.\nUrut");

        $sheet->mergeCells('B'.$headRow1.':B'.$headRow2);
        $sheet->setCellValue('B'.$headRow1, 'Nama Titik Meter');

        $sheet->mergeCells('C'.$headRow1.':D'.$headRow1);
        $sheet->setCellValue('C'.$headRow1, 'COUNTER (m³)');
        $sheet->setCellValue('C'.$headRow2, 'Bulan Ini');
        $sheet->setCellValue('D'.$headRow2, 'Bulan Lalu');

        $sheet->mergeCells('E'.$headRow1.':E'.$headRow2);
        $sheet->setCellValue('E'.$headRow1, "Jumlah Pengambilan\n(m³)");

        $sheet->mergeCells('F'.$headRow1.':F'.$headRow2);
        $sheet->setCellValue('F'.$headRow1, "Tarif\n(Rp/m³)");

        $sheet->mergeCells('G'.$headRow1.':G'.$headRow2);
        $sheet->setCellValue('G'.$headRow1, "Jumlah\n(Rp)");

        foreach (range('A', $lastCol) as $col) {
            $range = $col.$headRow1.':'.$col.$headRow2;
            $sheet->getStyle($range)->getFont()->setBold(true)->setSize(9);
            $sheet->getStyle($range)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
            $sheet->getStyle($range)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB(self::HEADER_FILL);
        }
        $sheet->getRowDimension($headRow1)->setRowHeight(24);
        $sheet->getRowDimension($headRow2)->setRowHeight(24);
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
            $sheet->setCellValue('G'.$r, $tg ? (float) $row['jumlah_sebelum_ppn'] : null);

            $sheet->getStyle('A'.$r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C'.$r.':F'.$r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C'.$r.':F'.$r)->getNumberFormat()->setFormatCode(self::NUM_FMT);
            $sheet->getStyle('G'.$r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('G'.$r)->getNumberFormat()->setFormatCode(self::NUM_FMT);

            $r++;
        }

        // Label ringkasan membentang penuh (A-F), nilainya di kolom JUMLAH.
        self::summaryRow($sheet, $r, 'Jumlah Total', (float) $area['subtotal'], self::TOTAL_FILL, true);

        if ($area['kena_ppn']) {
            self::summaryRow(
                $sheet,
                $r,
                'PPN '.number_format($area['persen_ppn'], 0, ',', '.').'%',
                (float) $area['ppn'],
                self::PPN_FILL,
                false
            );
            self::summaryRow($sheet, $r, 'Total', (float) $area['total'], self::TOTAL_FILL, true);
        }

        self::borders($sheet, 'A'.$dataStart.':'.$lastCol.($r - 1));
        $r++;

        self::fotoBlock($sheet, $r, $rows);

        $r += 3;
        self::ttdBlock($sheet, $r, $penandatangan);
    }

    private static function summaryRow(Worksheet $sheet, int &$r, string $label, float $nilai, string $fill, bool $bold): void
    {
        $lastCol = self::LAST_COL;
        $jumlahCol = self::JUMLAH_COL;

        $sheet->mergeCells('A'.$r.':F'.$r);
        $sheet->setCellValue('A'.$r, $label);
        $sheet->getStyle('A'.$r)->getFont()->setBold($bold);
        $sheet->getStyle('A'.$r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue($jumlahCol.$r, $nilai);
        $sheet->getStyle($jumlahCol.$r)->getFont()->setBold($bold);
        $sheet->getStyle($jumlahCol.$r)->getNumberFormat()->setFormatCode(self::NUM_FMT);
        $sheet->getStyle($jumlahCol.$r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getStyle('A'.$r.':'.$lastCol.$r)
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($fill);

        $sheet->getRowDimension($r)->setRowHeight(18);
        $r++;
    }

    /**
     * Bagi kolom A..LAST_COL menjadi $n slot berurutan (tanpa tumpang tindih)
     * dengan lebar total (piksel) sedekat mungkin sama rata. Dijamin selalu
     * menghasilkan tepat $n kelompok kolom yang valid untuk mergeCells.
     */
    private static function splitColumnsEqually(array $cols, array $widths, int $jumlah): array
    {
        $total = array_sum($widths);
        $numCols = count($cols);
        $target = $total / $jumlah;

        $groups = [];
        $current = [];
        $cumulative = 0;
        $groupIndex = 1;

        foreach ($cols as $i => $col) {
            $current[] = $col;
            $cumulative += $widths[$col];

            $colsUsed = $i + 1;
            $colsRemaining = $numCols - $colsUsed;
            $groupsRemaining = $jumlah - $groupIndex;

            if (
                $groupIndex < $jumlah
                && $colsRemaining >= $groupsRemaining
                && $cumulative >= $target * $groupIndex
            ) {
                $groups[] = $current;
                $current = [];
                $groupIndex++;
            }
        }
        if (! empty($current)) {
            $groups[] = $current;
        }

        return $groups;
    }

    /**
     * Slot foto per baris, meniru tata letak di PDF: kalau fotonya cuma 1-3,
     * "tabel mini" foto itu dipersempit (persentase sama seperti di PDF) lalu
     * dipusatkan secara horizontal di tengah tabel; kalau 4 foto, lebar penuh
     * dibagi rata seperti biasa.
     */
    private static function fotoSlots(Worksheet $sheet, int $jumlah): array
    {
        $cols = range('A', self::LAST_COL);
        $widths = [];
        foreach ($cols as $col) {
            $widths[$col] = max(1, $sheet->getColumnDimension($col)->getWidth());
        }
        $total = array_sum($widths);

        if ($jumlah >= 4) {
            return self::splitColumnsEqually($cols, $widths, $jumlah);
        }

        // Persentase lebar "tabel mini" -- sama seperti $lebarTabel di PDF.
        $persenLebar = [1 => 30, 2 => 55, 3 => 78][$jumlah] ?? 100;
        $targetUsed = $total * $persenLebar / 100;
        $margin = ($total - $targetUsed) / 2;

        // Cari kolom awal: kolom pertama yang membuat kumulatif lebar melewati margin kiri.
        $cumulative = 0;
        $startIdx = count($cols) - 1;
        foreach ($cols as $i => $col) {
            if ($cumulative + $widths[$col] > $margin) {
                $startIdx = $i;
                break;
            }
            $cumulative += $widths[$col];
        }

        // Dari kolom awal, kumpulkan kolom sampai lebar targetUsed terpenuhi.
        $cumulative2 = 0;
        $endIdx = $startIdx;
        for ($i = $startIdx; $i < count($cols); $i++) {
            $cumulative2 += $widths[$cols[$i]];
            $endIdx = $i;
            if ($cumulative2 >= $targetUsed) {
                break;
            }
        }

        // Pastikan jumlah kolom aktif minimal sama dengan jumlah foto.
        if (($endIdx - $startIdx + 1) < $jumlah) {
            $endIdx = min(count($cols) - 1, $startIdx + $jumlah - 1);
        }

        $activeCols = array_slice($cols, $startIdx, $endIdx - $startIdx + 1);
        $activeWidths = array_intersect_key($widths, array_flip($activeCols));

        return self::splitColumnsEqually($activeCols, $activeWidths, $jumlah);
    }

    private static function fotoBlock(Worksheet $sheet, int &$r, $rows): void
    {
        $barisFoto = $rows
            ->filter(fn ($row) => $row['tagihan'] && self::resolveFotoPath($row['tagihan']) !== null)
            ->values();

        if ($barisFoto->isEmpty()) {
            return;
        }

        $sheet->setCellValue('A'.$r, 'Foto Meter :');
        $sheet->getStyle('A'.$r)->getFont()->setBold(true)->setSize(11);
        $r += 1;

        $chunkIndex = 0;
        $totalChunks = $barisFoto->chunk(4)->count();

        foreach ($barisFoto->chunk(4) as $chunk) {
            $chunkArr = $chunk->values();
            $slots = self::fotoSlots($sheet, $chunkArr->count());
            $labelRow = $r;
            $photoRow = $r + 1;

            foreach ($chunkArr as $i => $row) {
                $slot = $slots[$i];
                [$startCol, $endCol] = [reset($slot), end($slot)];

                $titikIdx = $rows->search(fn ($item) => ($item['titik_meter']->id ?? null) === ($row['titik_meter']->id ?? null));
                $noUrutFoto = $titikIdx !== false ? ($titikIdx + 1) : (($chunkIndex * 4) + $i + 1);

                $sheet->mergeCells($startCol.$labelRow.':'.$endCol.$labelRow);
                $sheet->setCellValue($startCol.$labelRow, $noUrutFoto.'. '.$row['titik_meter']->nama);
                $sheet->getStyle($startCol.$labelRow)->getFont()->setBold(true);
                $sheet->getStyle($startCol.$labelRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
                $sheet->getStyle($startCol.$labelRow.':'.$endCol.$labelRow)
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::HEADER_FILL);
                self::borders($sheet, $startCol.$labelRow.':'.$endCol.$labelRow);

                $sheet->mergeCells($startCol.$photoRow.':'.$endCol.$photoRow);
                $sheet->getStyle($startCol.$photoRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                self::borders($sheet, $startCol.$photoRow.':'.$endCol.$photoRow);

                $drawing = new Drawing;
                $drawing->setName('foto-'.$labelRow.'-'.$i);
                $drawing->setPath(self::resolveFotoPath($row['tagihan']));
                $drawing->setResizeProportional(true);
                $drawing->setHeight(95);

                $slotWidthPx = array_sum(array_map(
                    fn ($col) => $sheet->getColumnDimension($col)->getWidth() * 7,
                    $slot
                ));

                if ($drawing->getWidth() + 10 > $slotWidthPx) {
                    $drawing->setWidth((int) floor($slotWidthPx - 10));
                }

                // Center horizontal di dalam slot, center vertikal pada tinggi baris 105px.
                $drawing->setCoordinates($startCol.$photoRow);
                $drawing->setOffsetX(max(2, (int) (($slotWidthPx - $drawing->getWidth()) / 2)));
                $drawing->setOffsetY(max(2, (int) ((105 - $drawing->getHeight()) / 2)));
                $drawing->setWorksheet($sheet);
            }

            $sheet->getRowDimension($labelRow)->setRowHeight(26);
            $sheet->getRowDimension($photoRow)->setRowHeight(105);
            $r = $photoRow + 1;
            $chunkIndex++;

            if ($chunkIndex < $totalChunks) {
                $sheet->getRowDimension($r)->setRowHeight(8);
                $r++;
            }
        }
    }

    private static function borders(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getBorders()->applyFromArray([
            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF333333']],
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

    private static function ttdBlock(Worksheet $sheet, int &$r, $penandatangan): void
    {
        if (! $penandatangan || $penandatangan->isEmpty()) {
            return;
        }

        [$leftStart, $leftEnd] = ['A', 'C'];
        [$rightStart, $rightEnd] = ['E', 'G'];

        $pLeft = $penandatangan->first();
        $pRight = $penandatangan->count() > 1 ? $penandatangan->get(1) : null;

        $tempat = $pLeft->tempat ?: ($pRight ? $pRight->tempat : 'Paiton');
        $tanggal = Carbon::now()->locale('id')->translatedFormat('d F Y');
        $dateText = ($tempat ? $tempat.', ' : '').$tanggal;

        $rowDate = $r;
        $sheet->mergeCells($rightStart.$rowDate.':'.$rightEnd.$rowDate);
        $sheet->setCellValue($rightStart.$rowDate, $dateText);
        $sheet->getStyle($rightStart.$rowDate)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($rowDate)->setRowHeight(18);
        $r++;

        $sheet->getRowDimension($r)->setRowHeight(8);
        $r++;

        $baris = [
            ['Mengetahui,', $pRight ? 'Menyetujui,' : ''],
            [$pLeft ? ($pLeft->jabatan ?: '') : '', $pRight ? ($pRight->jabatan ?: '') : ''],
        ];

        foreach ($baris as [$kiri, $kanan]) {
            $sheet->mergeCells($leftStart.$r.':'.$leftEnd.$r);
            $sheet->setCellValue($leftStart.$r, $kiri);
            $sheet->getStyle($leftStart.$r)->getFont()->setBold(true);
            $sheet->getStyle($leftStart.$r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells($rightStart.$r.':'.$rightEnd.$r);
            $sheet->setCellValue($rightStart.$r, $kanan);
            $sheet->getStyle($rightStart.$r)->getFont()->setBold(true);
            $sheet->getStyle($rightStart.$r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getRowDimension($r)->setRowHeight(18);
            $r++;
        }

        $sheet->getRowDimension($r)->setRowHeight(68);
        $r++;

        $namaRow = $r;
        $sheet->mergeCells($leftStart.$namaRow.':'.$leftEnd.$namaRow);
        $sheet->setCellValue($leftStart.$namaRow, $pLeft ? ($pLeft->nama ?: '...................................') : '');
        $sheet->getStyle($leftStart.$namaRow)->getFont()->setBold(true);
        $sheet->getStyle($leftStart.$namaRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        if ($pRight) {
            $sheet->mergeCells($rightStart.$namaRow.':'.$rightEnd.$namaRow);
            $sheet->setCellValue($rightStart.$namaRow, $pRight->nama ?: '...................................');
            $sheet->getStyle($rightStart.$namaRow)->getFont()->setBold(true);
            $sheet->getStyle($rightStart.$namaRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $sheet->getRowDimension($namaRow)->setRowHeight(18);

        $r = $namaRow + 2;
    }
}