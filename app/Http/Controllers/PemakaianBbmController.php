<?php

namespace App\Http\Controllers;

use App\Exports\PemakaianBbmExport;
use App\Exports\PertanggungjawabanExport;
use App\Models\HargaBbm;
use App\Models\Kendaraan;
use App\Models\PemakaianBbm;
use App\Models\Penandatangan;
use App\Models\PertanggungjawabanPeriode;
use App\Services\PemakaianBbmRekapService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class PemakaianBbmController extends Controller
{
    public function __construct(private PemakaianBbmRekapService $rekapService)
    {
    }

    /**
     * Halaman input transaksi harian.
     */
    public function index(Request $request)
    {
        $pemakaianBbms = PemakaianBbm::with(['kendaraan', 'hargaBbm', 'pencatat'])
            ->orderByDesc('tanggal')
            ->paginate(20);

        $kendaraans = Kendaraan::orderBy('plat_nomor')->get();

        // Daftar riwayat harga BBM (urut terbaru dulu), dipakai JS buat nyari harga
        // yang berlaku pada tanggal transaksi + jenis BBM yang dipilih.
        $hargaBbmList = HargaBbm::orderByDesc('tanggal_berlaku')->get()->map(function ($h) {
            return [
                'tanggal_berlaku'      => $h->tanggal_berlaku->format('Y-m-d'),
                'harga_pertamax'       => (float) $h->harga_pertamax,
                'harga_pertadex'       => (float) $h->harga_pertadex,
                'harga_dexlite'        => (float) $h->harga_dexlite,
                'harga_pertamax_turbo' => (float) $h->harga_pertamax_turbo,
            ];
        });

        $edit = null;
        if ($request->has('edit')) {
            $edit = PemakaianBbm::with('hargaBbm')->find($request->query('edit'));
        }

        return view('pemakaian-bbm.index', compact('pemakaianBbms', 'kendaraans', 'edit', 'hargaBbmList'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePemakaian($request);

        PemakaianBbm::create($this->buildPayload($validated));

        return redirect()->route('pemakaian-bbm.index')
            ->with('success', 'Data pemakaian BBM berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $pemakaian = PemakaianBbm::findOrFail($id);

        $validated = $this->validatePemakaian($request);

        $pemakaian->update($this->buildPayload($validated));

        return redirect()->route('pemakaian-bbm.index')
            ->with('success', 'Data pemakaian BBM berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $pemakaian = PemakaianBbm::findOrFail($id);
        $pemakaian->delete();

        return redirect()->route('pemakaian-bbm.index')
            ->with('success', 'Data pemakaian BBM berhasil dihapus.');
    }

    /**
     * Halaman rekap periode + export.
     */
    public function rekapan(Request $request)
    {
        $tanggalAwal  = $request->query('tanggal_awal');
        $tanggalAkhir = $request->query('tanggal_akhir');
        $groups       = [];
        $grandTotal   = null;
        $periodeLabel = null;

        if ($tanggalAwal && $tanggalAkhir) {
            $validated    = $this->validatePeriode($request);
            $data         = $this->rekapService->build($validated['tanggal_awal'], $validated['tanggal_akhir']);
            $groups       = $data['groups'];
            $grandTotal   = $data['grandTotal'];
            $periodeLabel = $data['periodeLabel'];
        }

        return view('pemakaian-bbm.rekapan', compact(
            'tanggalAwal', 'tanggalAkhir', 'groups', 'grandTotal', 'periodeLabel'
        ));
    }

    public function exportExcel(Request $request)
    {
        $validated = $this->validatePeriode($request);
        $data = $this->rekapService->build($validated['tanggal_awal'], $validated['tanggal_akhir']);

        $filename = 'pemakaian-bbm_' . $validated['tanggal_awal'] . '_sd_' . $validated['tanggal_akhir'] . '.xlsx';

        return Excel::download(new PemakaianBbmExport($data), $filename);
    }

    public function exportPdf(Request $request)
    {
        $validated = $this->validatePeriode($request);
        $data = $this->rekapService->build($validated['tanggal_awal'], $validated['tanggal_akhir']);

        $filename = 'pemakaian-bbm_' . $validated['tanggal_awal'] . '_sd_' . $validated['tanggal_akhir'] . '.pdf';

        $pdf = Pdf::loadView('rekapan.pemakaian-bbm.rekap-pdf', $data)->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

        /**
     * Halaman laporan Pertanggungjawaban.
     *
     * Periode disimpan permanen di tabel pertanggungjawaban_periode (bukan lagi
     * form array "minggu" per-request). Tanggal yang sudah dipakai suatu periode
     * otomatis tidak bisa dipakai lagi oleh periode lain (siapapun user-nya) sampai
     * admin menghapusnya (lihat destroyPeriode()).
     */
    public function pertanggungjawaban(Request $request)
    {
        $bulanLabel = $request->query('bulan_label');
 
        $periodes = PertanggungjawabanPeriode::query()
            ->when($bulanLabel, fn ($q) => $q->where('bulan_label', $bulanLabel))
            ->orderBy('tanggal_awal')
            ->get();
 
        $weeks         = [];
        $keterangan    = null;
        $penandatangan = Penandatangan::where('jabatan', Penandatangan::ASMAN)->first();
 
        if ($bulanLabel && $periodes->isNotEmpty()) {
            $weeks      = $this->buildWeeks($periodes);
            $keterangan = $this->buildKeterangan($weeks);
        }
 
        // Dropdown daftar label bulan yang sudah pernah diinput, biar gampang dipilih ulang
        $bulanOptions = PertanggungjawabanPeriode::select('bulan_label')
            ->distinct()
            ->orderByDesc('bulan_label')
            ->pluck('bulan_label');
 
        return view('pemakaian-bbm.pertanggungjawaban', compact(
            'bulanLabel', 'periodes', 'weeks', 'keterangan', 'penandatangan', 'bulanOptions'
        ));
    }
 
    /**
     * Tambah 1 periode baru. Bisa dilakukan siapa saja (bukan admin-only) - setiap
     * user boleh generate laporan untuk rentang tanggal yang dia mau. Ditolak kalau
     * rentang tanggalnya tumpang tindih dengan periode yang sudah ada.
     */
    public function storePeriode(Request $request)
    {
        $validated = $request->validate([
            'bulan_label'   => 'required|string|max:50',
            'tanggal_awal'  => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
        ], [
            'bulan_label.required'         => 'Label bulan wajib diisi.',
            'tanggal_awal.required'        => 'Tanggal awal wajib diisi.',
            'tanggal_akhir.required'       => 'Tanggal akhir wajib diisi.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
        ]);
 
        $overlap = PertanggungjawabanPeriode::where('tanggal_awal', '<=', $validated['tanggal_akhir'])
            ->where('tanggal_akhir', '>=', $validated['tanggal_awal'])
            ->exists();
 
        if ($overlap) {
            return redirect()
                ->route('pemakaian-bbm.pertanggungjawaban', ['bulan_label' => $validated['bulan_label']])
                ->withErrors([
                    'tanggal_awal' => 'Sebagian atau seluruh tanggal pada rentang tersebut sudah dipakai di periode lain. Hapus periode itu dulu (tab Riwayat) kalau memang salah input.',
                ])
                ->withInput();
        }
 
        PertanggungjawabanPeriode::create($validated);
 
        return redirect()
            ->route('pemakaian-bbm.pertanggungjawaban', ['bulan_label' => $validated['bulan_label']])
            ->with('success', 'Periode berhasil ditambahkan.');
    }
 
    /**
     * Hapus 1 periode. Cuma admin. Tanggalnya jadi bebas dipakai lagi setelah ini.
     */
    public function destroyPeriode($id)
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'Hanya admin yang bisa menghapus periode.');
 
        $periode    = PertanggungjawabanPeriode::findOrFail($id);
        $bulanLabel = $periode->bulan_label;
        $periode->delete();
 
        return redirect()
            ->route('pemakaian-bbm.pertanggungjawaban', ['bulan_label' => $bulanLabel])
            ->with('success', 'Periode berhasil dihapus, tanggalnya bisa dipilih lagi.');
    }

    public function exportPertanggungjawabanExcel(Request $request)
    {
        $data = $this->buildPertanggungjawabanData($request);

        $filename = 'pertanggungjawaban-bbm_' . \Illuminate\Support\Str::slug($data['bulanLabel']) . '.xlsx';

        return Excel::download(new PertanggungjawabanExport($data), $filename);
    }

    public function exportPertanggungjawabanPdf(Request $request)
    {
        $data = $this->buildPertanggungjawabanData($request);

        $filename = 'pertanggungjawaban-bbm_' . \Illuminate\Support\Str::slug($data['bulanLabel']) . '.pdf';

        $pdf = Pdf::loadView('rekapan.pemakaian-bbm.pertanggungjawaban-pdf', $data)->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    /**
     * Halaman Riwayat: daftar semua periode laporan Pertanggungjawaban yang pernah
     * dibuat (semua bulan). Semua user bisa lihat, cuma admin yang bisa hapus
     * (lihat destroyPeriode()).
     */
    public function riwayat()
    {
        $periodes = PertanggungjawabanPeriode::orderByDesc('tanggal_awal')->get();
 
        return view('pemakaian-bbm.riwayat', compact('periodes'));
    }

    /* =======================================================
     * Helper privat
     * ======================================================= */

    private function validatePemakaian(Request $request): array
    {
        return $request->validate([
            'tanggal'          => 'required|date',
            'kendaraan_id'     => 'required|exists:kendaraan,id',
            'jenis_bbm'        => 'required|in:pertamax,pertadex,dexlite,pertamax_turbo',
            'lokasi_pembelian' => 'required|in:paiton,luar_paiton',
            'liter'            => 'nullable|numeric|min:0',
            'service_oli'      => 'nullable|numeric|min:0',
            'jasa'             => 'nullable|numeric|min:0',
        ], [
            'tanggal.required'          => 'Tanggal wajib diisi.',
            'tanggal.date'              => 'Format tanggal tidak valid.',
            'kendaraan_id.required'     => 'Kendaraan wajib dipilih.',
            'kendaraan_id.exists'       => 'Kendaraan tidak valid.',
            'jenis_bbm.required'        => 'Jenis BBM wajib dipilih.',
            'jenis_bbm.in'              => 'Jenis BBM tidak valid.',
            'lokasi_pembelian.required' => 'Lokasi pembelian wajib dipilih.',
            'liter.numeric'             => 'Liter harus berupa angka.',
            'service_oli.numeric'       => 'Sparepart Consumable harus berupa angka.',
            'jasa.numeric'              => 'Jasa harus berupa angka.',
        ]);
    }

    /**
     * Cari harga BBM yang sedang berlaku untuk sebuah tanggal (baris dengan
     * tanggal_berlaku terbaru yang <= tanggal transaksi), lalu hitung Rp dari
     * kolom harga sesuai jenis BBM yang dipilih.
     */
    private function buildPayload(array $validated): array
    {
        $tanggal  = $validated['tanggal'];
        $jenisBbm = $validated['jenis_bbm'];

        $hargaBbm = HargaBbm::where('tanggal_berlaku', '<=', $tanggal)
            ->orderByDesc('tanggal_berlaku')
            ->first();

        if (!$hargaBbm) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'jenis_bbm' => 'Belum ada data harga BBM yang berlaku untuk tanggal ini. Tambahkan dulu di menu Harga BBM.',
            ]);
        }

        $kolomHarga    = 'harga_' . $jenisBbm;
        $hargaPerLiter = (float) $hargaBbm->{$kolomHarga};

        $liter      = (float) ($validated['liter'] ?? 0);
        $serviceOli = (float) ($validated['service_oli'] ?? 0);
        $jasa       = (float) ($validated['jasa'] ?? 0);

        $rp     = $liter * $hargaPerLiter;
        $jumlah = $rp + $serviceOli + $jasa;

        return [
            'kendaraan_id'     => $validated['kendaraan_id'],
            'harga_bbm_id'     => $hargaBbm->id,
            'jenis_bbm'        => $jenisBbm,
            'tanggal'          => $tanggal,
            'lokasi_pembelian' => $validated['lokasi_pembelian'],
            'liter'            => $liter,
            'rp'               => $rp,
            'service_oli'      => $serviceOli,
            'jasa'             => $jasa,
            'jumlah'           => $jumlah,
            'dicatat_oleh'     => auth()->id(),
        ];
    }

    /**
     * Validasi periode Rekapan. Tanggal akhir tetap wajib >= tanggal awal
     * (data integrity), tapi pesan error dibuat jelas biar user ngerti salahnya di mana.
     */
    private function validatePeriode(Request $request): array
    {
        return $request->validate([
            'tanggal_awal'  => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
        ], [
            'tanggal_awal.required'      => 'Tanggal awal wajib diisi.',
            'tanggal_awal.date'          => 'Format tanggal awal tidak valid.',
            'tanggal_akhir.required'     => 'Tanggal akhir wajib diisi.',
            'tanggal_akhir.date'         => 'Format tanggal akhir tidak valid.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal. Cek kembali kedua tanggal yang diisi.',
        ]);
    }

    

    /**
     * Validasi form Pertanggungjawaban. Tanggal akhir tiap minggu tetap wajib
     * >= tanggal awal minggu itu, dengan pesan error yang jelas per-minggu.
     */
    private function validatePertanggungjawaban(Request $request): array
    {
        return $request->validate([
            'bulan_label'             => 'required|string|max:50',
            'minggu'                  => 'required|array|min:1',
            'minggu.*.tanggal_awal'   => 'required|date',
            'minggu.*.tanggal_akhir'  => 'required|date|after_or_equal:minggu.*.tanggal_awal',
        ], [
            'bulan_label.required'                => 'Label bulan wajib diisi.',
            'minggu.required'                     => 'Minimal harus ada 1 minggu yang diisi.',
            'minggu.*.tanggal_awal.required'      => 'Tanggal awal Minggu ke-:position wajib diisi.',
            'minggu.*.tanggal_awal.date'          => 'Tanggal awal Minggu ke-:position tidak valid.',
            'minggu.*.tanggal_akhir.required'     => 'Tanggal akhir Minggu ke-:position wajib diisi.',
            'minggu.*.tanggal_akhir.date'         => 'Tanggal akhir Minggu ke-:position tidak valid.',
            'minggu.*.tanggal_akhir.after_or_equal' => 'Tanggal akhir Minggu ke-:position tidak boleh sebelum tanggal awalnya. Cek kembali kedua tanggal di minggu itu.',
        ]);
    }

    /**
     * Bangun data tiap minggu (grouped table) memakai rekap service yang sudah ada.
     * Ikut hitung total gabungan Roda Empat + Roda Dua (exclude Roda Tiga) per minggu -
     * ini yang jadi acuan angka "Pemakaian BBM untuk di Paiton" di bagian Keterangan.
     */
    private function buildWeeks(array $minggus): array
    {
        $weeks = [];

        foreach ($minggus as $i => $minggu) {
            $data = $this->rekapService->build($minggu['tanggal_awal'], $minggu['tanggal_akhir']);

            $groupsTanpaRodaTiga = array_values(array_filter(
                $data['groups'],
                fn ($g) => !str_contains($g['label'], 'Roda Tiga')
            ));

            $totalGabungan = ['liter' => 0, 'rp' => 0];
            foreach ($groupsTanpaRodaTiga as $g) {
                $totalGabungan['liter'] += $g['total']['liter'];
                $totalGabungan['rp'] += $g['total']['rp'];
            }

            $weeks[] = [
                'no'                 => $i + 1,
                'periodeLabel'       => $data['periodeLabel'],
                'groups'             => $data['groups'],
                'grandTotal'         => $data['grandTotal'],
                'totalGabungan'      => $totalGabungan, // = "Jumlah 1 + 2" minggu ini
            ];
        }

        return $weeks;
    }

    /**
     * Bagian "Keterangan" laporan sekarang cuma 1 baris: "Pemakaian BBM untuk di
     * Paiton", nilainya adalah total "Jumlah 1 + 2" (Roda Empat + Roda Dua) dari
     * seluruh minggu yang dipilih, dijumlahkan.
     */
    private function buildKeterangan(array $weeks): array
    {
        $paiton = 0.0;

        foreach ($weeks as $week) {
            $paiton += $week['totalGabungan']['rp'];
        }

        return [
            'paiton' => $paiton,
        ];
    }

    /**
     * Kumpulkan semua data yang dibutuhkan export Excel/PDF Pertanggungjawaban.
     */
    private function buildPertanggungjawabanData(Request $request): array
    {
        $validated = $this->validatePertanggungjawaban($request);

        $weeks         = $this->buildWeeks($validated['minggu']);
        $keterangan    = $this->buildKeterangan($weeks);
        $penandatangan = Penandatangan::where('jabatan', Penandatangan::ASMAN)->first();

        return [
            'bulanLabel'    => $validated['bulan_label'],
            'weeks'         => $weeks,
            'keterangan'    => $keterangan,
            'penandatangan' => $penandatangan,
        ];
    }
}