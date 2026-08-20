<?php

namespace App\Http\Controllers;

use App\Exports\EtollExport;
use App\Models\PemegangKendaraan;
use App\Models\PemakaianEtoll;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class EtollController extends Controller
{
    private const NAMA_BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function index(Request $request)
    {
        $tab = $request->query('tab', 'input');

        if ($tab === 'rekapan') {
            $report = $this->buildReport($request);

            return view('pemakaian-etoll.index', [
                'tab' => 'rekapan',
                'report' => $report,
            ]);
        }

        $pemakaianEtolls = PemakaianEtoll::with('pemegangKendaraan')->latest('tanggal')->latest('id')->get();
        $pemegangKendaraans = PemegangKendaraan::orderBy('nama')->get();

        $edit = null;
        if ($request->has('edit')) {
            $edit = PemakaianEtoll::findOrFail($request->query('edit'));
        }

        return view('pemakaian-etoll.index', [
            'tab' => 'input',
            'pemakaianEtolls' => $pemakaianEtolls,
            'pemegangKendaraans' => $pemegangKendaraans,
            'edit' => $edit,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pemegang_kendaraan_id' => 'required|exists:pemegang_kendaraan,id',
            'tanggal' => 'required|date',
            'nominal' => 'required|numeric|min:0',
        ]);

        $validated['dicatat_oleh'] = Auth::id();

        PemakaianEtoll::create($validated);

        return redirect()->route('pemakaian-etoll.index')
            ->with('success', 'Data pemakaian e-toll berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $pemakaianEtoll = PemakaianEtoll::findOrFail($id);

        $validated = $request->validate([
            'pemegang_kendaraan_id' => 'required|exists:pemegang_kendaraan,id',
            'tanggal' => 'required|date',
            'nominal' => 'required|numeric|min:0',
        ]);

        $pemakaianEtoll->update($validated);

        return redirect()->route('pemakaian-etoll.index')
            ->with('success', 'Data pemakaian e-toll berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $pemakaianEtoll = PemakaianEtoll::findOrFail($id);
        $pemakaianEtoll->delete();

        return redirect()->route('pemakaian-etoll.index')
            ->with('success', 'Data pemakaian e-toll berhasil dihapus.');
    }

    public function exportPdf(Request $request)
    {
        $periode = $this->parsePeriode($request);
        if (!$periode) {
            return redirect()->route('pemakaian-etoll.index', ['tab' => 'rekapan'])
                ->with('error', 'Pilih bulan dan tahun terlebih dahulu untuk export.');
        }
        [$bulan, $tahun] = $periode;

        $data = $this->buildLaporanData($bulan, $tahun);

        $pdf = Pdf::loadView('exports.etoll-pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('rekap-etoll-' . strtolower($data['bulanNama']) . '-' . $tahun . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $periode = $this->parsePeriode($request);
        if (!$periode) {
            return redirect()->route('pemakaian-etoll.index', ['tab' => 'rekapan'])
                ->with('error', 'Pilih bulan dan tahun terlebih dahulu untuk export.');
        }
        [$bulan, $tahun] = $periode;

        $data = $this->buildLaporanData($bulan, $tahun);

        return Excel::download(
            new EtollExport($data),
            'rekap-etoll-' . strtolower($data['bulanNama']) . '-' . $tahun . '.xlsx'
        );
    }

    /**
     * Parse query string 'bulan' (format YYYY-MM, dari <input type="month">).
     * Wajib diisi & valid, tidak ada default otomatis.
     *
     * @return array{0: int, 1: int}|null
     */
    private function parsePeriode(Request $request): ?array
    {
        $bulan = $request->query('bulan');

        if (!$bulan || !preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            return null;
        }

        [$tahun, $bulanNum] = array_map('intval', explode('-', $bulan));

        return [$bulanNum, $tahun];
    }

    /**
     * Susun data buat tab Rekapan (ditampilkan di layar). Kalau bulan belum
     * dipilih, kembalikan data kosong supaya view bisa nampilin placeholder
     * "silakan pilih bulan dulu", bukan langsung nampilin laporan bulan berjalan.
     */
    private function buildReport(Request $request): array
    {
        $bulanRaw = $request->query('bulan');
        $periode = $this->parsePeriode($request);

        if (!$periode) {
            return [
                'bulanRaw' => $bulanRaw,
                'pemegangs' => collect(),
                'rows' => [],
                'totalPerPemegang' => [],
                'totalKeseluruhan' => 0,
                'bulan' => null,
                'tahun' => null,
                'bulanNama' => null,
                'periodeLabel' => null,
            ];
        }

        [$bulan, $tahun] = $periode;
        $data = $this->buildLaporanData($bulan, $tahun);
        $data['bulanRaw'] = $bulanRaw;
        $data['periodeLabel'] = $data['bulanNama'] . ' ' . $tahun;

        return $data;
    }

    /**
     * Susun data laporan pivot per tanggal: baris = tanggal (1 s/d akhir bulan),
     * kolom = nama pemegang kendaraan. Filter tetap per bulan & tahun.
     */
    private function buildLaporanData(int $bulan, int $tahun): array
    {
        $pemegangs = PemegangKendaraan::orderBy('id')->get();

        $transaksi = PemakaianEtoll::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        $jumlahHari = \Carbon\Carbon::create($tahun, $bulan, 1)->daysInMonth;

        // nilai[tanggal][pemegang_id] = total nominal di tanggal itu
        $nilai = [];
        for ($tgl = 1; $tgl <= $jumlahHari; $tgl++) {
            $nilai[$tgl] = array_fill_keys($pemegangs->pluck('id')->all(), 0);
        }

        foreach ($transaksi as $item) {
            $tgl = $item->tanggal->day;
            $nilai[$tgl][$item->pemegang_kendaraan_id] = ($nilai[$tgl][$item->pemegang_kendaraan_id] ?? 0) + (float) $item->nominal;
        }

        $rows = [];
        $totalPerPemegang = array_fill_keys($pemegangs->pluck('id')->all(), 0);
        $totalKeseluruhan = 0;

        for ($tgl = 1; $tgl <= $jumlahHari; $tgl++) {
            $rowTotal = array_sum($nilai[$tgl]);

            $rows[] = [
                'tanggal' => $tgl,
                'nilai' => $nilai[$tgl],
                'total' => $rowTotal,
            ];

            foreach ($nilai[$tgl] as $pemegangId => $val) {
                $totalPerPemegang[$pemegangId] += $val;
            }
            $totalKeseluruhan += $rowTotal;
        }

        return [
            'pemegangs' => $pemegangs,
            'rows' => $rows,
            'totalPerPemegang' => $totalPerPemegang,
            'totalKeseluruhan' => $totalKeseluruhan,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'bulanNama' => self::NAMA_BULAN[$bulan] ?? (string) $bulan,
        ];
    }
}