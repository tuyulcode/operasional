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
        $pemakaianEtolls = PemakaianEtoll::with('pemegangKendaraan')->latest('tanggal')->latest('id')->get();
        $pemegangKendaraans = PemegangKendaraan::orderBy('nama')->get();

        $edit = null;
        if ($request->has('edit')) {
            $edit = PemakaianEtoll::findOrFail($request->query('edit'));
        }

        return view('pemakaian-etoll.index', compact('pemakaianEtolls', 'pemegangKendaraans', 'edit'));
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
        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);

        $data = $this->buildLaporanData($bulan, $tahun);

        $pdf = Pdf::loadView('exports.etoll-pdf', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('rekap-etoll-' . strtolower($data['bulanNama']) . '-' . $tahun . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);

        $data = $this->buildLaporanData($bulan, $tahun);

        return Excel::download(
            new EtollExport($data),
            'rekap-etoll-' . strtolower($data['bulanNama']) . '-' . $tahun . '.xlsx'
        );
    }

    /**
     * Tentukan Minggu ke berapa (1-5) untuk sebuah tanggal, berdasarkan minggu kalender
     * asli (Senin s/d Minggu) — bukan sekadar potong rata tiap 7 hari dari tanggal 1.
     * Kalau dalam sebulan butuh lebih dari 5 kelompok minggu (bisa terjadi kalau
     * tanggal 1 jatuh di akhir minggu, misal Sabtu/Minggu), sisa hari di minggu ke-6
     * digabung ke Minggu-5.
     */
    private function mingguKe(\Carbon\Carbon $tanggal): int
    {
        $awalBulan = $tanggal->copy()->startOfMonth();
        $isoWeekday = $awalBulan->dayOfWeekIso; // 1 = Senin, ..., 7 = Minggu
        $panjangMingguPertama = $isoWeekday === 7 ? 1 : 8 - $isoWeekday;

        if ($tanggal->day <= $panjangMingguPertama) {
            return 1;
        }

        $sisaHari = $tanggal->day - $panjangMingguPertama;
        $minggu = 1 + (int) ceil($sisaHari / 7);

        return min($minggu, 5);
    }

    /**
     * Susun data laporan mingguan (Minggu-1 s/d Minggu-5) per pemegang kendaraan
     * untuk bulan & tahun tertentu. Pembagian minggu mengikuti kalender asli
     * (Senin-Minggu), lihat mingguKe().
     */
    private function buildLaporanData(int $bulan, int $tahun): array
    {
        $pemegangs = PemegangKendaraan::orderBy('id')->get();

        $transaksi = PemakaianEtoll::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get()
            ->groupBy('pemegang_kendaraan_id');

        $rows = [];
        $totalMinggu = array_fill(1, 5, 0);
        $totalKeseluruhan = 0;

        foreach ($pemegangs as $p) {
            $mingguan = array_fill(1, 5, 0);
            $items = $transaksi->get($p->id, collect());

            foreach ($items as $item) {
                $minggu = $this->mingguKe($item->tanggal);
                $mingguan[$minggu] += (float) $item->nominal;
            }

            $jumlahBaris = array_sum($mingguan);

            $rows[] = [
                'nama' => $p->nama,
                'minggu' => $mingguan,
                'jumlah' => $jumlahBaris,
            ];

            foreach ($mingguan as $m => $val) {
                $totalMinggu[$m] += $val;
            }
            $totalKeseluruhan += $jumlahBaris;
        }

        return [
            'rows' => $rows,
            'totalMinggu' => $totalMinggu,
            'totalKeseluruhan' => $totalKeseluruhan,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'bulanNama' => self::NAMA_BULAN[$bulan] ?? (string) $bulan,
        ];
    }
}