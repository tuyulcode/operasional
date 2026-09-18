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
            self::headerBlock($sheet, '', '-');
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

    private static function headerBlock(Worksheet $sheet, string $periodeLabel, string $lokasi): int
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

        $judul = [
            ['Rekap Biaya Pemakaian Air', 14, true, 26],
            [$lokasi, 12, true, 20],
            ['Bulan : '.$periodeLabel, 11, true, 18],
        ];

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

        // Total hanya menghitung baris AKTIF saja, konsisten dengan baris
        // yang ditampilkan di tabel (baris nonaktif tidak tampil & tidak dihitung).
        $subtotal = $rows->sum(fn ($row) => max(0, (float) (($row['tagihan']->jumlah ?? 0) - ($row['tagihan']->ppn_nominal ?? 0))));
        $ppnValue = $rows->sum(fn ($row) => (float) ($row['tagihan']->ppn_nominal ?? 0));
        $total = $subtotal + $ppnValue;
        $firstPpn = $rows->first(fn ($row) => $row['tagihan'] && (float) $row['tagihan']->ppn_persentase > 0);
        $ppnPersen = $firstPpn ? (float) $firstPpn['tagihan']->ppn_persentase : 0;

        $sheet->getColumnDimension('A')->setWidth(7);   // No. Urut
        $sheet->getColumnDimension('B')->setWidth(30);  // Nama Titik Meter
        $sheet->getColumnDimension('C')->setWidth(12);  // Bulan Ini
        $sheet->getColumnDimension('D')->setWidth(12);  // Bulan Lalu
        $sheet->getColumnDimension('E')->setWidth(15);  // Jumlah Pengambilan
        $sheet->getColumnDimension('F')->setWidth(14);  // Tarif
        $sheet->getColumnDimension('G')->setWidth(17);  // Jumlah Rp

        $lokasi = $area['area']->alamat
            ? $area['area']->nama.' - '.$area['area']->alamat
            : $area['area']->nama;

        $r = self::headerBlock($sheet, $periodeLabel, $lokasi);
        $dataStart = $r;

        $headRow1 = $r;
        $headRow2 = $r + 1;

        $sheet->mergeCells('A'.$headRow1.':A'.$headRow2);
        $sheet->setCellValue('A'.$headRow1, "No.\nUrut");

        $sheet->mergeCells('B'.$headRow1.':B'.$headRow2);
        $sheet->setCellValue('B'.$headRow1, 'Nama Titik Meter');

        $sheet->mergeCells('C'.$headRow1.':D'.$headRow1);
        $sheet->setCellValue('C'.$headRow1, 'COUNTER  M³');
        $sheet->setCellValue('C'.$headRow2, 'Bulan Ini');
        $sheet->setCellValue('D'.$headRow2, 'Bulan Lalu');

        $sheet->mergeCells('E'.$headRow1.':E'.$headRow2);
        $sheet->setCellValue('E'.$headRow1, "Jumlah\nPengambilan");

        $sheet->mergeCells('F'.$headRow1.':F'.$headRow2);
        $sheet->setCellValue('F'.$headRow1, "TARIF\nRp / M³");

        $sheet->mergeCells('G'.$headRow1.':G'.$headRow2);
        $sheet->setCellValue('G'.$headRow1, "JUMLAH\nRp");

        foreach (range('A', $lastCol) as $col) {
            $range = $col.$headRow1.':'.$col.$headRow2;
            $sheet->getStyle($range)->getFont()->setBold(true);
            $sheet->getStyle($range)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
            $sheet->getStyle($range)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB(self::HEADER_FILL);
        }
        $sheet->getRowDimension($headRow1)->setRowHeight(18);
        $sheet->getRowDimension($headRow2)->setRowHeight(18);
        $r = $headRow2 + 1;

        $no = 0;
        foreach ($rows as $row) {
            $tg = $row['tagihan'] ?? null;
            $no++;

            // FIX: kolom "JUMLAH Rp" per baris sebelumnya menampilkan
            // $tg->jumlah (SUDAH termasuk PPN per transaksi), padahal
            // baris "Jumlah Total" di bawah dihitung SEBELUM PPN
            // (RekapanController::buildReport -> subtotal = jumlah - ppn_nominal).
            // Akibatnya total jumlah per baris tidak pernah sama dengan
            // "Jumlah Total". Sekarang dikurangi ppn_nominal-nya dulu
            // supaya konsisten dengan ringkasan di bawah tabel.
            $jumlahSebelumPpn = $tg ? ((float) $tg->jumlah - (float) $tg->ppn_nominal) : null;

            $sheet->setCellValue('A'.$r, $no);
            $sheet->setCellValue('B'.$r, $row['titik_meter']->nama);
            $sheet->setCellValue('C'.$r, $tg ? (int) round((float) $tg->meter_ini) : null);
            $sheet->setCellValue('D'.$r, $tg ? (int) round((float) $tg->meter_lalu) : null);
            $sheet->setCellValue('E'.$r, $tg ? (int) round((float) $tg->pemakaian) : null);
            $sheet->setCellValue('F'.$r, $tg ? (float) $tg->tarif : (float) ($row['titik_meter']->tarif_harga ?? 0));
            $sheet->setCellValue('G'.$r, $jumlahSebelumPpn);

            $sheet->getStyle('A'.$r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C'.$r.':F'.$r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C'.$r.':F'.$r)->getNumberFormat()->setFormatCode(self::NUM_FMT);
            $sheet->getStyle('G'.$r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('G'.$r)->getNumberFormat()->setFormatCode(self::NUM_FMT);

            $r++;
        }

        // Label ringkasan membentang penuh (A-F), nilainya di kolom JUMLAH.
        self::summaryRow($sheet, $r, 'Jumlah Total', (float) $subtotal, self::TOTAL_FILL, true);

        if ($area['kena_ppn']) {
            self::summaryRow(
                $sheet,
                $r,
                'PPN '.number_format($ppnPersen, 0, ',', '.').'%',
                (float) $ppnValue,
                self::PPN_FILL,
                false
            );
            self::summaryRow($sheet, $r, 'Total', (float) $total, self::TOTAL_FILL, true);
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
     * Slot foto per baris (tabel 7 kolom, A-G). 1-3 foto dipusatkan
     * di tengah tabel, 4 foto memakai lebar penuh secara merata.
     */
    private static function fotoSlots(int $jumlah): array
    {
        return match ($jumlah) {
            1 => [['C', 'D', 'E']],
            2 => [['B', 'C'], ['E', 'F']],
            3 => [['B', 'C'], ['D', 'E'], ['F', 'G']],
            default => self::fotoGridColumns(self::LAST_COL, 4),
        };
    }

    private static function fotoGridColumns(string $lastCol, int $n): array
    {
        $cols = range('A', $lastCol);
        $groupSize = (int) max(1, ceil(count($cols) / $n));
        $chunks = array_values(array_chunk($cols, $groupSize));

        while (count($chunks) < $n) {
            $chunks[] = [end($cols)];
        }

        return array_slice($chunks, 0, $n);
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
        $sheet->getStyle('A'.$r)->getFont()->setBold(true);
        $r += 1;

        $chunkIndex = 0;

        foreach ($barisFoto->chunk(4) as $chunk) {
            $chunkArr = $chunk->values();
            $slots = self::fotoSlots($chunkArr->count());
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
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle($startCol.$labelRow.':'.$endCol.$labelRow)
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::HEADER_FILL);

                $sheet->mergeCells($startCol.$photoRow.':'.$endCol.$photoRow);
                $sheet->getStyle($startCol.$photoRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                self::borders($sheet, $startCol.$labelRow.':'.$endCol.$photoRow);

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

                // Center horizontal di dalam slot, center vertikal pada tinggi baris 100px.
                $drawing->setCoordinates($startCol.$photoRow);
                $drawing->setOffsetX(max(2, (int) (($slotWidthPx - $drawing->getWidth()) / 2)));
                $drawing->setOffsetY(max(2, (int) ((100 - $drawing->getHeight()) / 2)));
                $drawing->setWorksheet($sheet);
            }

            $sheet->getRowDimension($labelRow)->setRowHeight(18);
            $sheet->getRowDimension($photoRow)->setRowHeight(100);
            $r = $photoRow + 1;
            $chunkIndex++;
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
            ['Menyetujui,', $pRight ? 'Mengusulkan,' : ''],
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
        $sheet->getStyle($leftStart.$namaRow)->getFont()->setBold(true)->setUnderline(true);
        $sheet->getStyle($leftStart.$namaRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        if ($pRight) {
            $sheet->mergeCells($rightStart.$namaRow.':'.$rightEnd.$namaRow);
            $sheet->setCellValue($rightStart.$namaRow, $pRight->nama ?: '...................................');
            $sheet->getStyle($rightStart.$namaRow)->getFont()->setBold(true)->setUnderline(true);
            $sheet->getStyle($rightStart.$namaRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $sheet->getRowDimension($namaRow)->setRowHeight(18);

        $r = $namaRow + 2;
    }
}