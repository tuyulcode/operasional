<?php

namespace App\Services;

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

    private const COLS = [
        ['No', 22],
        ['Titik Meter', 124],
        ['Meter Lalu', 58],
        ['Meter Ini', 58],
        ['Pemakaian', 68],
        ['Tarif', 68],
        ['Jumlah', 95],
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
        $this->pageNo++;
    }

    private function ensure(float $lines = 1): void
    {
        if ($this->y - $lines * 11 < self::B) {
            $this->newPage();
        }
    }

    private function esc(string $s): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
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

    private function cell(int $colIndex, string $value, bool $rightAlign = false): void
    {
        [$name, $width] = self::COLS[$colIndex];
        $x = self::M;
        for ($i = 0; $i < $colIndex; $i++) {
            $x += self::COLS[$i][1];
        }
        if ($rightAlign) {
            $x += $width - (strlen($value) * 4);
        }
        $this->content .= sprintf("BT /F1 8 Tf %.2f %.2f Td (%s) Tj ET\n", $x, $this->y, $this->esc($value));
    }

    private function hr(): void
    {
        $this->content .= sprintf("%.2f %.2f m %.2f %.2f l S\n", self::M, $this->y, self::W - self::M, $this->y);
        $this->y -= 6;
    }

    private function drawHeader(): void
    {
        $this->ensure();
        foreach (self::COLS as $i => [$name, $width]) {
            $x = self::M;
            for ($j = 0; $j < $i; $j++) {
                $x += self::COLS[$j][1];
            }
            $this->content .= sprintf("BT /F2 8 Tf %.2f %.2f Td (%s) Tj ET\n", $x, $this->y, $this->esc($name));
        }
        $this->y -= 10;
        $this->hr();
    }

    private function row(array $vals): void
    {
        $this->ensure();
        for ($i = 0; $i < count($vals); $i++) {
            $this->cell($i, (string) $vals[$i], $i >= 2);
        }
        $this->y -= 9;
    }

    private function fmtRp($value, int $decimals = 0): string
    {
        return 'Rp ' . number_format((float) $value, $decimals, ',', '.');
    }

    public static function generate(array $report): string
    {
        $pdf = new self();

        $pdf->text($report['title'] ?? 'Rekapan Tagihan Air', 14, self::M, true);
        $pdf->text('Periode: ' . ($report['periodeLabel'] ?? '-'), 10);
        $pdf->y -= 4;

        foreach ($report['data'] as $area) {
            $pdf->ensure(4);
            $pdf->text('Area: ' . $area['area']->nama, 10, self::M, true);
            $pdf->y -= 2;
            $pdf->drawHeader();

            foreach ($area['rows'] as $i => $row) {
                $tagihan = $row['tagihan'];
                $pdf->row([
                    $i + 1,
                    $row['titik_meter']->nama,
                    $tagihan ? number_format($tagihan->meter_lalu, 2, ',', '.') : '-',
                    $tagihan ? number_format($tagihan->meter_ini, 2, ',', '.') : '-',
                    $tagihan ? number_format($tagihan->pemakaian, 2, ',', '.') : '-',
                    $tagihan ? $pdf->fmtRp($tagihan->tarif) : '-',
                    $tagihan ? $pdf->fmtRp($tagihan->jumlah) : '-',
                ]);
            }

            $pdf->ensure(3);
            $pdf->hr();
            $pdf->text(
                'Subtotal ' . $area['area']->nama . ': ' . $pdf->fmtRp($area['subtotal']) .
                '   (' . number_format($area['total_pemakaian'], 2, ',', '.') . ' m3)',
                9
            );
            if ($area['kena_ppn']) {
                $pdf->text(
                    'PPN ' . number_format($area['persen_ppn'], 0, ',', '.') . '%: ' . $pdf->fmtRp($area['ppn']),
                    9
                );
                $pdf->text(
                    'TOTAL ' . $area['area']->nama . ': ' . $pdf->fmtRp($area['total']),
                    9, self::M, true
                );
            }
            $pdf->y -= 6;
        }

        $pdf->ensure(2);
        $pdf->hr();
        $pdf->text(
            'GRAND TOTAL: ' . $pdf->fmtRp($report['grandTotal']) .
            '   (' . number_format($report['grandPemakaian'] ?? 0, 2, ',', '.') . ' m3)',
            11, self::M, true
        );

        $pdf->signatureBlock($report['penandatangan'] ?? []);

        return $pdf->output();
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

    private function signatureBlock(iterable $penandatangan): void
    {
        if (empty($penandatangan)) {
            return;
        }

        $this->ensure(9);
        $this->y -= 8;

        $title = 'Mengetahui / Menyetujui';
        $this->text($title, 10, (self::W - strlen($title) * 5) / 2, true);
        $this->y -= 26;

        $baseY = $this->y;
        $colW = (self::W - 2 * self::M) / 2;
        $xLeft = self::M;
        $xRight = self::M + $colW;

        foreach ($penandatangan as $i => $row) {
            $x = $i === 0 ? $xLeft : $xRight;
            $this->place($row->jabatan, 9, $x, $baseY, true);
            $nama = $row->nama ?: '........................................';
            $this->place($nama, 10, $x, $baseY - 36);
            $this->hline($x, $baseY - 40, 150);
        }

        $this->y = $baseY - 54;

        $tempat = $penandatangan[0]->tempat ?? '';
        $tanggal = \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y');
        $dateLine = ($tempat ? $tempat . ', ' : '') . $tanggal;
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

        $kids = implode(' ', array_map(fn ($i) => (4 + $i * 2) . ' 0 R', range(0, $n - 1)));
        $objects[] = "<< /Type /Pages /Kids [ $kids ] /Count $n >>";

        for ($i = 0; $i < $n; $i++) {
            $stream = $this->pages[$i];
            $objects[] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "endstream";
            $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 " . self::W . " " . self::H . "] " .
                "/Resources << /Font << /F1 " . $fontRegular . " 0 R /F2 " . $fontBold . " 0 R >> >> " .
                "/Contents " . (3 + $i * 2) . " 0 R >>";
        }

        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

        $output = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $obj) {
            $offsets[] = strlen($output);
            $output .= ($i + 1) . " 0 obj\n" . $obj . "\nendobj\n";
        }

        $xrefPos = strlen($output);
        $output .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $output .= sprintf("%010d 00000 n \n", $offset);
        }
        $output .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $output .= "startxref\n" . $xrefPos . "\n%%EOF\n";

        return $output;
    }
}