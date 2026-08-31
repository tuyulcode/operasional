<?php

namespace App\Http\Controllers;

use App\Exports\EtollExport;
use App\Models\PemegangKendaraan;
use App\Models\PemakaianEtoll;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class EtollController extends Controller
{
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
        $rentang = $this->parseRentangTanggal($request);
        if (!$rentang) {
            return redirect()->route('pemakaian-etoll.index', ['tab' => 'rekapan'])
                ->with('error', 'Pilih tanggal awal dan tanggal akhir terlebih dahulu untuk export.');
        }
        [$awal, $akhir] = $rentang;

        $data = $this->buildLaporanData($awal, $akhir);

        $pdf = Pdf::loadView('exports.etoll-pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        $namaFile = 'rekap-etoll-' . $awal->format('Ymd') . '-' . $akhir->format('Ymd') . '.pdf';

        return $pdf->stream($namaFile);
    }

    public function exportExcel(Request $request)
    {
        $rentang = $this->parseRentangTanggal($request);
        if (!$rentang) {
            return redirect()->route('pemakaian-etoll.index', ['tab' => 'rekapan'])
                ->with('error', 'Pilih tanggal awal dan tanggal akhir terlebih dahulu untuk export.');
        }
        [$awal, $akhir] = $rentang;

        $data = $this->buildLaporanData($awal, $akhir);

        $namaFile = 'rekap-etoll-' . $awal->format('Ymd') . '-' . $akhir->format('Ymd') . '.xlsx';

        return Excel::download(new EtollExport($data), $namaFile);
    }

    /**
     * Parse & validasi query string 'tanggal_awal' dan 'tanggal_akhir' (format Y-m-d,
     * dari <input type="date">). Wajib diisi keduanya, valid, dan tanggal_akhir >=
     * tanggal_awal. Tidak ada default otomatis.
     *
     * @return array{0: Carbon, 1: Carbon}|null
     */
    private function parseRentangTanggal(Request $request): ?array
    {
        $awalRaw = $request->query('tanggal_awal');
        $akhirRaw = $request->query('tanggal_akhir');

        if (!$awalRaw || !$akhirRaw) {
            return null;
        }

        try {
            $awal = Carbon::createFromFormat('Y-m-d', $awalRaw)->startOfDay();
            $akhir = Carbon::createFromFormat('Y-m-d', $akhirRaw)->startOfDay();
        } catch (\Exception $e) {
            return null;
        }

        if ($akhir->lt($awal)) {
            return null;
        }

        return [$awal, $akhir];
    }

    /**
     * Susun data buat tab Rekapan (ditampilkan di layar). Kalau tanggal awal/akhir
     * belum dipilih atau tidak valid, kembalikan data kosong supaya view bisa
     * nampilin placeholder "silakan pilih tanggal dulu".
     */
    private function buildReport(Request $request): array
    {
        $tanggalAwalRaw = $request->query('tanggal_awal');
        $tanggalAkhirRaw = $request->query('tanggal_akhir');
        $rentang = $this->parseRentangTanggal($request);

        if (!$rentang) {
            return [
                'tanggalAwalRaw' => $tanggalAwalRaw,
                'tanggalAkhirRaw' => $tanggalAkhirRaw,
                'pemegangs' => collect(),
                'rows' => [],
                'totalPerPemegang' => [],
                'totalKeseluruhan' => 0,
                'awal' => null,
                'akhir' => null,
                'periodeLabel' => null,
            ];
        }

        [$awal, $akhir] = $rentang;
        $data = $this->buildLaporanData($awal, $akhir);
        $data['tanggalAwalRaw'] = $tanggalAwalRaw;
        $data['tanggalAkhirRaw'] = $tanggalAkhirRaw;

        return $data;
    }

    /**
     * Susun data laporan pivot per tanggal (bisa lintas bulan/tahun): baris = setiap
     * tanggal dari $awal s/d $akhir (inklusif), kolom = nama pemegang kendaraan.
     */
    private function buildLaporanData(Carbon $awal, Carbon $akhir): array
    {
        $pemegangs = PemegangKendaraan::orderBy('id')->get();
        $pemegangIds = $pemegangs->pluck('id')->all();

        $transaksi = PemakaianEtoll::whereBetween('tanggal', [
            $awal->format('Y-m-d'),
            $akhir->format('Y-m-d'),
        ])->get();

        $periode = CarbonPeriod::create($awal, $akhir);

        // nilai[Y-m-d][pemegang_id] = total nominal di tanggal itu
        $nilai = [];
        foreach ($periode as $tgl) {
            $nilai[$tgl->format('Y-m-d')] = array_fill_keys($pemegangIds, 0);
        }

        foreach ($transaksi as $item) {
            $key = $item->tanggal->format('Y-m-d');
            if (!isset($nilai[$key])) {
                continue;
            }
            $nilai[$key][$item->pemegang_kendaraan_id] = ($nilai[$key][$item->pemegang_kendaraan_id] ?? 0) + (float) $item->nominal;
        }

        $rows = [];
        $totalPerPemegang = array_fill_keys($pemegangIds, 0);
        $totalKeseluruhan = 0;

        foreach ($periode as $tgl) {
            $key = $tgl->format('Y-m-d');
            $rowTotal = array_sum($nilai[$key]);

            $rows[] = [
                'tanggalKey' => $key,
                'tanggal' => $tgl->format('d/m'),
                'nilai' => $nilai[$key],
                'total' => $rowTotal,
            ];

            foreach ($nilai[$key] as $pemegangId => $val) {
                $totalPerPemegang[$pemegangId] += $val;
            }
            $totalKeseluruhan += $rowTotal;
        }

        return [
            'pemegangs' => $pemegangs,
            'rows' => $rows,
            'totalPerPemegang' => $totalPerPemegang,
            'totalKeseluruhan' => $totalKeseluruhan,
            'awal' => $awal,
            'akhir' => $akhir,
            'periodeLabel' => $awal->translatedFormat('d F Y') . ' - ' . $akhir->translatedFormat('d F Y'),
        ];
    }
}