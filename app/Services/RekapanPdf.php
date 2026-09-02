<?php

namespace App\Services;

use Carbon\Carbon;

class RekapanPdf
{
    private const W = 595.28;

    private const H = 841.89;

    private const M = 36;

    private const B = 40;

    private array $pages = [];

    private string $content = '';

    private float $y;

    private int $pageNo = 0;

    private ?float $gridTop = null;

    private array $gridXs = [];

    private const COLS = [
        ['No', 22],
        ['Nama Titik Meter', 118],
        ['Bulan Ini', 48],
        ['Bulan Lalu', 48],
        ['Pengambilan', 50],
        ['Tarif', 58],
        ['Jumlah', 88],
    ];

    public function __construct()
    {
        $this->newPage();
    }

    private function newPage(): void
    {
        if ($this->content !== '') {
            $this->pages[] = $this->content;
        }
        $this->content = '';
        $this->y = self::H - self::M;
        $this->gridTop = null;
        $this->pageNo++;
    }

    private function ensure(float $lines = 1): void
    {
        if ($this->y - $lines * 12.15 < self::B) {
            $this->newPage();
        }
    }

    private function ensureH(float $points): void
    {
        if ($this->y - $points < self::B) {
            $this->newPage();
        }
    }

    private function esc(string $s): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
    }

    private function displayName(string $s): string
    {
        // "PT." -> "PT " (hilangkan titik setelah PT)
        return preg_replace('/\bPT\.\s*/i', 'PT ', trim($s));
    }

    private function text(string $s, float $size = 8, ?float $x = null, bool $bold = false): void
    {
        $x = $x ?? self::M;
        $this->content .= sprintf(
            "BT /F%s %.2f Tf %.2f %.2f Td (%s) Tj ET\n",
            $bold ? 2 : 1,
            $size,
            $x,
            $this->y,
            $this->esc($s)
        );
        $this->y -= $size * 1.35;
    }

    private function hr(): void
    {
        $width = array_sum(array_column(self::COLS, 1));
        $this->content .= sprintf("%.2f %.2f m %.2f %.2f l S\n", self::M, $this->y, self::M + $width, $this->y);
        $this->y -= 6;
    }

    private function vline(float $x, float $fromY, float $toY): void
    {
        $this->content .= sprintf("%.2f %.2f m %.2f %.2f l S\n", $x, $fromY, $x, $toY);
    }

    private function gridBegin(float $left, array $widths): void
    {
        $this->gridXs = [];
        $x = $left;
        foreach ($widths as $w) {
            $this->gridXs[] = $x;
            $x += $w;
        }
        $this->gridXs[] = $x;
        $this->gridTop = $this->y;
        $this->content .= sprintf("%.2f %.2f m %.2f %.2f l S\n", $left, $this->y, $x, $this->y);
    }

    private function gridVerticals(float $fromY, float $toY, array $skip = []): void
    {
        for ($i = 0, $n = count($this->gridXs); $i < $n; $i++) {
            if (in_array($i, $skip, true)) {
                continue;
            }
            $this->vline($this->gridXs[$i], $fromY, $toY);
        }
    }

    private function gridRow(float $rowHeight, array $cells): void
    {
        $this->ensureH($rowHeight + 9);

        if ($this->gridTop === null) {
            $this->gridTop = $this->y;
            $this->content .= sprintf("%.2f %.2f m %.2f %.2f l S\n", $this->gridXs[0], $this->y, end($this->gridXs), $this->y);
        }

        $skip = [];
        $textY = $this->y - 7;
        foreach ($cells as $c) {
            $col = $c['col'] ?? null;
            if ($col === null) {
                continue;
            }
            $value = (string) ($c['value'] ?? '');
            $right = $c['right'] ?? false;
            $center = $c['center'] ?? false;
            $bold = $c['bold'] ?? false;
            $span = $c['span'] ?? 1;

            if ($span > 1) {
                for ($i = $col + 1; $i < $col + $span; $i++) {
                    $skip[] = $i;
                }
            }

            $x = $this->gridXs[$col];
            if ($span <= 1 && ($right || $center)) {
                $cellW = $this->gridXs[$col + 1] - $this->gridXs[$col];
                $textW = strlen($value) * 4.4;
                $x += $center ? ($cellW - $textW) / 2 : $cellW - $textW;
            }
            $this->content .= sprintf("BT /F%d 8 Tf %.2f %.2f Td (%s) Tj ET\n", $bold ? 2 : 1, $x, $textY, $this->esc($value));
        }

        $this->y -= $rowHeight;
        $this->content .= sprintf("%.2f %.2f m %.2f %.2f l S\n", $this->gridXs[0], $this->y, end($this->gridXs), $this->y);
        $this->gridVerticals($this->y + $rowHeight, $this->y, $skip);
    }

    private function drawHeader(): void
    {
        $this->ensureH(28);
        $this->gridBegin(self::M, array_column(self::COLS, 1));

        $this->gridRow(11, [
            ['col' => 0, 'value' => 'No', 'bold' => true],
            ['col' => 1, 'value' => 'Nama Titik Meter', 'bold' => true],
            ['col' => 2, 'value' => 'COUNTER M3', 'span' => 2, 'bold' => true],
            ['col' => 4, 'value' => 'Pengambilan', 'bold' => true],
            ['col' => 5, 'value' => 'Tarif', 'bold' => true],
            ['col' => 6, 'value' => 'Jumlah', 'bold' => true],
        ]);
        $this->gridRow(11, [
            ['col' => 2, 'value' => 'Bulan Ini', 'bold' => true],
            ['col' => 3, 'value' => 'Bulan Lalu', 'bold' => true],
        ]);
    }

    private function row(array $vals): void
    {
        $cells = [];
        foreach ($vals as $i => $v) {
            $cells[] = [
                'col' => $i,
                'value' => (string) $v,
                // Bulan Ini, Bulan Lalu, Pengambilan -> rata tengah
                'center' => $i >= 2 && $i <= 4,
                // Tarif, Jumlah -> tetap rata kanan (nominal uang)
                'right' => $i >= 5,
            ];
        }
        $this->gridRow(11, $cells);
    }

    private function fmtRp($value, int $decimals = 0): string
    {
        return 'Rp '.number_format((float) $value, $decimals, ',', '.');
    }

    public static function generate(array $report): string
    {
        $pdf = new self;

        $pdf->text($report['title'] ?? 'Rekapan Tagihan Air', 14, self::M, true);
        $pdf->text('Periode: '.($report['periodeLabel'] ?? '-'), 10);
        $pdf->y -= 4;

        foreach ($report['data'] as $area) {
            if (($area['jml_titik'] ?? $area['rows']->count()) === 1) {
                $pdf->drawVerticalArea($area, $report['periodeLabel'] ?? '-');

                continue;
            }

            $pdf->ensure(4);
            $pdf->text('Area: '.$pdf->displayName($area['area']->nama), 10, self::M, true);
            $pdf->y -= 2;
            $pdf->drawHeader();

            foreach ($area['rows'] as $i => $row) {
                $tagihan = $row['tagihan'];
                $pdf->row([
                    $i + 1,
                    $row['titik_meter']->nama,
                    $tagihan ? (int) round((float) $tagihan->meter_ini) : '-',
                    $tagihan ? (int) round((float) $tagihan->meter_lalu) : '-',
                    $tagihan ? (int) round((float) $tagihan->pemakaian) : '-',
                    $tagihan ? $pdf->fmtRp($tagihan->tarif) : '-',
                    $tagihan ? $pdf->fmtRp($tagihan->jumlah) : '-',
                ]);
            }

            $pdf->gridRow(11, [
                ['col' => 0, 'value' => 'Subtotal '.$pdf->displayName($area['area']->nama), 'span' => 4, 'bold' => true],
                ['col' => 4, 'value' => $area['total_pemakaian'] ? (int) round($area['total_pemakaian']) : '-', 'center' => true],
                ['col' => 6, 'value' => $pdf->fmtRp($area['subtotal']), 'right' => true, 'bold' => true],
            ]);

            if ($area['kena_ppn']) {
                $pdf->gridRow(11, [
                    ['col' => 0, 'value' => 'PPN '.number_format($area['persen_ppn'], 0, ',', '.').'%', 'span' => 4, 'bold' => true],
                    ['col' => 6, 'value' => $pdf->fmtRp($area['ppn']), 'right' => true],
                ]);
                $pdf->gridRow(11, [
                    ['col' => 0, 'value' => 'TOTAL '.$pdf->displayName($area['area']->nama), 'span' => 4, 'bold' => true],
                    ['col' => 6, 'value' => $pdf->fmtRp($area['total']), 'right' => true, 'bold' => true],
                ]);
            }

            $pdf->y -= 4;
        }

        $pdf->signatureBlock($report['penandatangan'] ?? []);

        return $pdf->output();
    }

    private function drawVerticalArea(array $area, string $periodeLabel): void
    {
        $rowCount = 13 + ($area['kena_ppn'] ? 2 : 0);
        $this->ensureH(11 * $rowCount + 10);
        $this->y -= 4;
        $this->gridBegin(self::M, [150, 24, 190]);

        $this->gridRow(11, [
            ['col' => 0, 'value' => 'BIAYA PEMAKAIAN AIR', 'span' => 3, 'bold' => true],
        ]);

        $row1 = $area['rows']->first();
        $tg = $row1['tagihan'] ?? null;
        $ini = $tg ? (int) round((float) $tg->meter_ini) : 0;
        $lalu = $tg ? (int) round((float) $tg->meter_lalu) : 0;

        foreach ([
            ['Bulan', $periodeLabel],
            ['NAMA', $this->displayName($area['area']->nama)],
            ['ALAMAT', $area['area']->alamat ?: '-'],
            ['LOKASI FLOW METER', $row1['titik_meter']->nama],
        ] as [$label, $value]) {
            $this->gridRow(11, [
                ['col' => 0, 'value' => $label],
                ['col' => 1, 'value' => ':'],
                ['col' => 2, 'value' => $value],
            ]);
        }

        $this->gridRow(11, [
            ['col' => 0, 'value' => 'PERHITUNGAN PEMAKAIAN', 'span' => 3, 'bold' => true],
        ]);

        foreach ([
            ['Bulan ini', $ini],
            ['Bulan lalu', $lalu],
            ['Jumlah Pengambilan', $ini - $lalu],
            ['Meter Faktor', $tg ? number_format((float) $tg->meter_faktor, 0, ',', '.') : '0'],
            ['Jumlah Pengambilan', $tg ? (int) round((float) $tg->pemakaian) : 0],
            ['Tarif / M3', $this->fmtRp($tg->tarif ?? 0)],
            ['Subtotal (Rp)', $this->fmtRp($area['subtotal']), true],
        ] as $r) {
            $this->gridRow(11, [
                ['col' => 0, 'value' => $r[0], 'bold' => $r[2] ?? false],
                ['col' => 1, 'value' => ':'],
                ['col' => 2, 'value' => $r[1], 'bold' => $r[2] ?? false],
            ]);
        }

        if ($area['kena_ppn']) {
            $this->gridRow(11, [
                ['col' => 0, 'value' => 'PPN '.number_format($area['persen_ppn'], 0, ',', '.').'%'],
                ['col' => 1, 'value' => ':'],
                ['col' => 2, 'value' => $this->fmtRp($area['ppn'])],
            ]);
            $this->gridRow(11, [
                ['col' => 0, 'value' => 'Total (Rp)', 'bold' => true],
                ['col' => 1, 'value' => ':'],
                ['col' => 2, 'value' => $this->fmtRp($area['total']), 'bold' => true],
            ]);
        }

        $this->y -= 6;
    }

    private function place(string $s, float $size, float $x, float $y, bool $bold = false): void
    {
        $this->content .= sprintf(
            "BT /F%s %.2f Tf %.2f %.2f Td (%s) Tj ET\n",
            $bold ? 2 : 1,
            $size,
            $x,
            $y,
            $this->esc($s)
        );
    }

    private function hline(float $x, float $y, float $width): void
    {
        $this->content .= sprintf("%.2f %.2f m %.2f %.2f l S\n", $x, $y, $x + $width, $y);
    }

    private function centerX(string $s, float $colX, float $colW, float $charW = 4.7): float
    {
        return $colX + ($colW - strlen($s) * $charW) / 2;
    }

    private function signatureBlock(iterable $penandatangan): void
    {
        if (empty($penandatangan)) {
            return;
        }

        $this->ensureH(145);
        $this->y -= 6;

        $title = 'Mengetahui / Menyetujui';
        $this->text($title, 10, (self::W - strlen($title) * 5) / 2, true);
        $titleBase = $this->y;

        $baseY = $titleBase - 6;
        $colW = (self::W - 2 * self::M) / 2;
        $xLeft = self::M;
        $xRight = self::M + $colW;

        $lineY = $baseY - 80;
        $nameY = $lineY - 18;
        $lineWidth = 150;

        foreach ($penandatangan as $i => $row) {
            $x = $i === 0 ? $xLeft : $xRight;

            $this->place($row->jabatan, 9, $this->centerX($row->jabatan, $x, $colW, 4.7), $baseY, true);

            $nama = $row->nama ?: '........................................';
            $this->place($nama, 10, $this->centerX($nama, $x, $colW, 5.0), $nameY);

            $lineX = $x + ($colW - $lineWidth) / 2;
            $this->hline($lineX, $lineY, $lineWidth);
        }

        $this->y = $nameY - 22;

        $tempat = $penandatangan[0]->tempat ?? '';
        $tanggal = Carbon::now()->locale('id')->translatedFormat('d F Y');
        $dateLine = ($tempat ? $tempat.', ' : '').$tanggal;
        $this->text($dateLine, 9, (self::W - strlen($dateLine) * 4.5) / 2);
        $this->y -= 6;
    }

    private function output(): string
    {
        if ($this->content !== '') {
            $this->pages[] = $this->content;
        }

        $n = count($this->pages);
        $fontRegular = 3 + (2 * $n);
        $fontBold = $fontRegular + 1;

        $objects = [];
        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';

        $kids = implode(' ', array_map(fn ($i) => (4 + $i * 2).' 0 R', range(0, $n - 1)));
        $objects[] = "<< /Type /Pages /Kids [ $kids ] /Count $n >>";

        for ($i = 0; $i < $n; $i++) {
            $stream = $this->pages[$i];
            $objects[] = '<< /Length '.strlen($stream)." >>\nstream\n".$stream.'endstream';
            $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 '.self::W.' '.self::H.'] '.
                '/Resources << /Font << /F1 '.$fontRegular.' 0 R /F2 '.$fontBold.' 0 R >> >> '.
                '/Contents '.(3 + $i * 2).' 0 R >>';
        }

        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

        $output = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $obj) {
            $offsets[] = strlen($output);
            $output .= ($i + 1)." 0 obj\n".$obj."\nendobj\n";
        }

        $xrefPos = strlen($output);
        $output .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $output .= sprintf("%010d 00000 n \n", $offset);
        }
        $output .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\n";
        $output .= "startxref\n".$xrefPos."\n%%EOF\n";

        return $output;
    }
}