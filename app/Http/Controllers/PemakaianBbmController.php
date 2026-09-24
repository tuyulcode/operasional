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
use Carbon\Carbon;
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

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data pemakaian BBM berhasil disimpan.']);
        }

        return redirect()->route('pemakaian-bbm.index')
            ->with('success', 'Data pemakaian BBM berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $pemakaian = PemakaianBbm::findOrFail($id);

        $validated = $this->validatePemakaian($request);

        $pemakaian->update($this->buildPayload($validated));

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data pemakaian BBM berhasil diperbarui.']);
        }

        return redirect()->route('pemakaian-bbm.index')
            ->with('success', 'Data pemakaian BBM berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $pemakaian = PemakaianBbm::findOrFail($id);
        $pemakaian->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data pemakaian BBM berhasil dihapus.']);
        }

        return redirect()->route('pemakaian-bbm.index')
            ->with('success', 'Data pemakaian BBM berhasil dihapus.');
    }

    /**
     * Hitung ulang harga_bbm_id, rp, dan jumlah untuk SEMUA data pemakaian BBM
     * berdasarkan harga yang berlaku SAAT INI untuk tanggal masing-masing baris.
     *
     * Dipanggil dari tombol "Refresh" di halaman Input Data. Berguna ketika ada
     * perubahan pada data Harga BBM (tambah/edit/hapus harga dengan tanggal_berlaku
     * tertentu) yang membuat harga yang berlaku untuk tanggal-tanggal transaksi lama
     * jadi berubah, padahal rp/jumlah yang tersimpan di baris pemakaian itu masih
     * pakai harga yang lama.
     *
     * Fokus perubahan cuma pada baris yang memang kena dampak: baris yang hasil
     * hitung ulangnya (harga_bbm_id/rp/jumlah) sama persis dengan yang sudah
     * tersimpan TIDAK disentuh sama sekali (skip, tidak ikut ke-update_at-kan).
     */
    public function refreshHarga(Request $request)
    {
        $updated = 0;
        $dilewati = 0;

        $this->loadHargaCache();

        PemakaianBbm::orderBy('id')->chunkById(200, function ($items) use (&$updated, &$dilewati) {
            foreach ($items as $item) {
                $hargaBbm = $this->cariHargaBerlaku($item->tanggal);

                if (!$hargaBbm) {
                    // Tanggal transaksi ini belum ada harga BBM yang berlaku sama sekali,
                    // biarkan data lama apa adanya (jangan dipaksa jadi 0).
                    $dilewati++;
                    continue;
                }

                $hargaPerLiter = $this->hargaPerLiterUntuk($hargaBbm, $item->jenis_bbm);

                $rpBaru     = (float) $item->liter * $hargaPerLiter;
                $jumlahBaru = $rpBaru + (float) $item->service_oli + (float) $item->jasa;

                $berubah = (int) $item->harga_bbm_id !== (int) $hargaBbm->id
                    || round((float) $item->rp, 2) !== round($rpBaru, 2)
                    || round((float) $item->jumlah, 2) !== round($jumlahBaru, 2);

                if (!$berubah) {
                    $dilewati++;
                    continue;
                }

                $item->update([
                    'harga_bbm_id' => $hargaBbm->id,
                    'rp'           => $rpBaru,
                    'jumlah'       => $jumlahBaru,
                ]);
                $updated++;
            }
        });

        $pesan = $updated > 0
            ? "Berhasil refresh {$updated} data yang terdampak perubahan harga BBM ({$dilewati} data lain sudah sesuai)."
            : 'Semua data sudah sesuai dengan harga BBM terbaru, tidak ada yang perlu diperbarui.';

        return redirect()->route('pemakaian-bbm.index')->with('success', $pesan);
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
     * BEDA DARI SEBELUMNYA: user langsung isi tanggal_awal & tanggal_akhir buat
     * lihat preview - TIDAK ada lagi form "Tambah Periode" yang nyimpen ke DB
     * di titik ini. Preview murni dihitung on-the-fly dari rekapService, tanpa
     * nyimpen apa-apa. Baris pertanggungjawaban_periode baru dibuat nanti pas
     * user beneran klik Export Excel/PDF (lihat buildDanSimpanPertanggungjawabanData()).
     */
    public function pertanggungjawaban(Request $request)
    {
        $tanggalAwal  = $request->query('tanggal_awal');
        $tanggalAkhir = $request->query('tanggal_akhir');

        $weeks                = [];
        $keterangan           = null;
        $bulanLabel           = null;
        $keteranganBulanLabel = null;
        $penandatangan        = $this->getPenandatanganLaporan();

        if ($tanggalAwal && $tanggalAkhir) {
            $validated  = $this->validatePeriode($request);
            $weeks      = $this->buildWeeksForRange($validated['tanggal_awal'], $validated['tanggal_akhir']);
            $keterangan = $this->buildKeterangan($weeks);
            // $bulanLabel = rentang tanggal (dipakai di tempat lain / tab Riwayat).
            // $keteranganBulanLabel = nama bulan doang, khusus buat teks
            // "Laporan Pengeluaran BBM bulan ..." di bagian Keterangan.
            $bulanLabel           = $this->formatBulanLabel($validated['tanggal_awal'], $validated['tanggal_akhir']);
            $keteranganBulanLabel = $this->formatBulanSajaLabel($validated['tanggal_awal'], $validated['tanggal_akhir']);
        }

        // Semua rentang tanggal yang sudah pernah di-export sebelumnya (jadi baris
        // pertanggungjawaban_periode) - dipakai JS (Flatpickr) buat men-disable
        // tanggal itu di kalender tanggal_awal/tanggal_akhir, biar gak ke-export dobel.
        $periodeTerpakai = PertanggungjawabanPeriode::orderBy('tanggal_awal')
            ->get(['tanggal_awal', 'tanggal_akhir'])
            ->map(fn ($p) => [
                'from' => $p->tanggal_awal->format('Y-m-d'),
                'to'   => $p->tanggal_akhir->format('Y-m-d'),
            ])
            ->values();

        return view('pemakaian-bbm.pertanggungjawaban', compact(
            'tanggalAwal', 'tanggalAkhir', 'weeks', 'keterangan', 'bulanLabel', 'keteranganBulanLabel', 'penandatangan', 'periodeTerpakai'
        ));
    }

    /**
     * Hapus 1 periode. Cuma admin. Tanggalnya jadi bebas dipakai lagi setelah ini.
     */
    public function destroyPeriode($id)
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'Hanya admin yang bisa menghapus periode.');

        $periode = PertanggungjawabanPeriode::findOrFail($id);
        $periode->delete();

        return redirect()
            ->route('pemakaian-bbm.riwayat')
            ->with('success', 'Periode berhasil dihapus, tanggalnya bisa dipakai lagi.');
    }

    public function exportPertanggungjawabanExcel(Request $request)
    {
        $data = $this->buildDanSimpanPertanggungjawabanData($request);

        $filename = 'pertanggungjawaban-bbm_' . $data['tanggalAwal'] . '_sd_' . $data['tanggalAkhir'] . '.xlsx';

        return Excel::download(new PertanggungjawabanExport($data), $filename);
    }

    public function exportPertanggungjawabanPdf(Request $request)
    {
        $data = $this->buildDanSimpanPertanggungjawabanData($request);

        $filename = 'pertanggungjawaban-bbm_' . $data['tanggalAwal'] . '_sd_' . $data['tanggalAkhir'] . '.pdf';

        $pdf = Pdf::loadView('rekapan.pemakaian-bbm.pertanggungjawaban-pdf', $data)->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    /**
     * Halaman Riwayat: daftar semua periode laporan Pertanggungjawaban yang pernah
     * di-export (semua bulan). Semua user bisa lihat, cuma admin yang bisa hapus
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

        $hargaBbm = $this->cariHargaBerlaku($tanggal);

        if (!$hargaBbm) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'jenis_bbm' => 'Belum ada data harga BBM yang berlaku untuk tanggal ini. Tambahkan dulu di menu Harga BBM.',
            ]);
        }

        $hargaPerLiter = $this->hargaPerLiterUntuk($hargaBbm, $jenisBbm);

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

    private ?array $hargaCache = null;

    /**
     * Preload semua HargaBbm ke memory untuk operasi bulk (refreshHarga).
     * Setelah dipanggil, cariHargaBerlaku() akan pakai cache instead of query.
     */
    private function loadHargaCache(): void
    {
        $this->hargaCache = HargaBbm::orderByDesc('tanggal_berlaku')->get()->toArray();
    }

    /**
     * Cari baris HargaBbm yang berlaku untuk sebuah tanggal: baris dengan
     * tanggal_berlaku terbaru yang <= tanggal transaksi. Dipakai bareng-bareng
     * oleh buildPayload() (simpan/update satu baris) dan refreshHarga() (hitung
     * ulang massal), supaya aturan pencarian harganya selalu konsisten.
     */
    private function cariHargaBerlaku(string $tanggal): ?HargaBbm
    {
        if ($this->hargaCache !== null) {
            foreach ($this->hargaCache as $row) {
                if ($row['tanggal_berlaku'] <= $tanggal) {
                    return HargaBbm::hydrate([$row])->first();
                }
            }
            return null;
        }

        return HargaBbm::where('tanggal_berlaku', '<=', $tanggal)
            ->orderByDesc('tanggal_berlaku')
            ->first();
    }

    /**
     * Ambil harga per liter dari satu baris HargaBbm sesuai jenis_bbm. Kalau
     * jenis_bbm kosong/tidak dikenal (kolomnya nggak ada di HargaBbm), hasilnya 0
     * biar tidak error - dipakai juga oleh refreshHarga() buat data lama yang
     * jenis BBM-nya kosong ("-").
     */
    private function hargaPerLiterUntuk(HargaBbm $hargaBbm, ?string $jenisBbm): float
    {
        $kolomHarga = 'harga_' . $jenisBbm;

        return isset($hargaBbm->{$kolomHarga}) ? (float) $hargaBbm->{$kolomHarga} : 0.0;
    }

    /**
     * Validasi periode Rekapan / Pertanggungjawaban. Tanggal akhir tetap wajib
     * >= tanggal awal (data integrity), tapi pesan error dibuat jelas biar user
     * ngerti salahnya di mana.
     */
    private function validatePeriode(Request $request): array
    {
        return $request->validate([
            'tanggal_awal'  => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
        ], [
            'tanggal_awal.required'        => 'Tanggal awal wajib diisi.',
            'tanggal_awal.date'            => 'Format tanggal awal tidak valid.',
            'tanggal_akhir.required'       => 'Tanggal akhir wajib diisi.',
            'tanggal_akhir.date'           => 'Format tanggal akhir tidak valid.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal. Cek kembali kedua tanggal yang diisi.',
        ]);
    }

    /**
     * Bangun "weeks" (cuma 1 entri) untuk satu rentang tanggal_awal-tanggal_akhir,
     * dipakai baik oleh preview (pertanggungjawaban()) maupun export. Filter
     * Paiton-only diterapkan di dalam buildWeeks() -> rekapService->build().
     */
    private function buildWeeksForRange(string $tanggalAwal, string $tanggalAkhir): array
    {
        $range = (object) [
            'tanggal_awal'  => Carbon::parse($tanggalAwal),
            'tanggal_akhir' => Carbon::parse($tanggalAkhir),
        ];

        return $this->buildWeeks([$range]);
    }

    /**
     * Bangun data tiap periode (grouped table) memakai rekap service yang sudah ada.
     *
     * Beda dari tab Rekapan: laporan Pertanggungjawaban cuma boleh menghitung
     * transaksi yang lokasi pembelian-nya PAITON. Makanya build() dipanggil
     * dengan parameter ketiga 'paiton' di sini - tab Rekapan (method rekapan()
     * di atas) tetap manggil build() tanpa parameter itu, jadi tetap ambil
     * semua lokasi seperti biasa.
     *
     * KHUSUS laporan Pertanggungjawaban: nomor urut (kolom "no") tiap baris di-
     * generate ULANG jadi 1,2,3,... rata untuk satu grup kendaraan (Roda Empat /
     * Roda Tiga / Roda Dua), lintas unit - bukan mulai dari 1 lagi tiap unit.
     * Ini murni renumbering tampilan, tidak mengubah data asli dari rekapService.
     *
     * totalGabungan = SEMUA jenis kendaraan (Roda Empat + Roda Tiga + Roda Dua),
     * biar angkanya persis sama dengan baris "Jumlah Total" di tabel laporan.
     */
    private function buildWeeks(iterable $periodes): array
    {
        $weeks = [];

        foreach ($periodes as $i => $periode) {
            $awal  = $periode->tanggal_awal->format('Y-m-d');
            $akhir = $periode->tanggal_akhir->format('Y-m-d');

            $data = $this->rekapService->build($awal, $akhir, 'paiton');

            $groups = $this->renumberRowsPerGroup($data['groups']);

            $totalGabungan = ['liter' => 0, 'rp' => 0];
            foreach ($groups as $g) {
                $totalGabungan['liter'] += $g['total']['liter'];
                $totalGabungan['rp'] += $g['total']['rp'];
            }

            $weeks[] = [
                'no'            => $i + 1,
                'periodeLabel'  => $data['periodeLabel'],
                'groups'        => $groups,
                'grandTotal'    => $data['grandTotal'],
                'totalGabungan' => $totalGabungan, // = "Jumlah Total" periode ini
            ];
        }

        return $weeks;
    }

    /**
     * Generate ulang nomor urut ("no") tiap baris supaya rata 1,2,3,... per
     * grup kendaraan, lintas section/unit (bukan reset ke 1 tiap section).
     * Cuma dipakai untuk laporan Pertanggungjawaban - tab Rekapan biasa TIDAK
     * lewat sini, jadi nomornya di sana tetap seperti aslinya per unit.
     */
    private function renumberRowsPerGroup(array $groups): array
    {
        foreach ($groups as &$group) {
            $counter = 1;

            foreach ($group['sections'] as &$section) {
                foreach ($section['rows'] as &$row) {
                    $row['no'] = $counter++;
                }
            }
            unset($section, $row);
        }
        unset($group);

        return $groups;
    }

    /**
     * Bagian "Keterangan" laporan cuma 1 baris: "Pemakaian BBM untuk di Paiton",
     * nilainya adalah total "Jumlah Total" (Roda Empat + Roda Tiga + Roda Dua)
     * dari seluruh periode yang dipilih, dijumlahkan.
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
     * Satu-satunya tempat query penandatangan untuk laporan Pertanggungjawaban.
     */
    private function getPenandatanganLaporan(): ?Penandatangan
    {
        return Penandatangan::where('jabatan', Penandatangan::ASMAN)->first();
    }

    /**
     * Format label periode jadi rentang tanggal (bukan cuma nama bulan), dipakai
     * konsisten di preview & export (Excel/PDF) maupun kolom Bulan di tab Riwayat.
     *
     * - Satu bulan & tahun sama : "2 - 4 September 2026"
     * - Beda bulan, tahun sama  : "22 September - 4 Oktober 2026"
     * - Beda tahun              : "28 Desember 2026 - 3 Januari 2027"
     */
    private function formatBulanLabel(string $tanggalAwal, string $tanggalAkhir): string
    {
        $awal  = Carbon::parse($tanggalAwal)->locale('id');
        $akhir = Carbon::parse($tanggalAkhir)->locale('id');

        if ($awal->year !== $akhir->year) {
            return $awal->translatedFormat('j F Y') . ' - ' . $akhir->translatedFormat('j F Y');
        }

        if ($awal->month !== $akhir->month) {
            return $awal->translatedFormat('j F') . ' - ' . $akhir->translatedFormat('j F Y');
        }

        return $awal->day . ' - ' . $akhir->translatedFormat('j F Y');
    }

    /**
     * Format khusus buat teks "Laporan Pengeluaran BBM bulan ..." di bagian
     * Keterangan (beda dari formatBulanLabel() di atas yang formatnya rentang
     * tanggal). Ini cuma nama bulan (+ tahun kalau perlu):
     *
     * - Satu bulan & tahun sama : "September 2026"
     * - Beda bulan, tahun sama  : "September & Oktober 2026"
     * - Beda tahun              : "Desember 2026 & Januari 2027"
     */
    private function formatBulanSajaLabel(string $tanggalAwal, string $tanggalAkhir): string
    {
        $awal  = Carbon::parse($tanggalAwal)->locale('id');
        $akhir = Carbon::parse($tanggalAkhir)->locale('id');

        if ($awal->month === $akhir->month && $awal->year === $akhir->year) {
            return $awal->translatedFormat('F Y');
        }

        if ($awal->year === $akhir->year) {
            return $awal->translatedFormat('F') . ' & ' . $akhir->translatedFormat('F Y');
        }

        return $awal->translatedFormat('F Y') . ' & ' . $akhir->translatedFormat('F Y');
    }

    /**
     * Dipanggil pas user beneran klik Export Excel/PDF (bukan pas preview).
     *
     * 1. Validasi tanggal_awal/tanggal_akhir.
     * 2. Kalau rentang tanggal PERSIS SAMA dengan periode yang sudah pernah
     *    tercatat -> pakai ulang datanya (reuse), TIDAK bikin baris baru, TIDAK
     *    error. Ini yang bikin export Excel lalu PDF (atau berkali-kali) untuk
     *    rentang yang sama tetap bisa jalan terus.
     * 3. Kalau rentangnya BEDA tapi tumpang tindih sama periode lain -> tetap
     *    ditolak (mencegah data finansial dihitung dobel di laporan berbeda).
     * 4. Kalau ini beneran periode baru -> auto-generate bulan_label pakai
     *    formatBulanLabel() (format rentang tanggal), lalu SIMPAN sebagai baris
     *    pertanggungjawaban_periode baru - inilah titik tanggal ini resmi
     *    "terpakai" & muncul di tab Riwayat.
     * 5. Baru bangun data laporannya buat di-render ke Excel/PDF.
     */
    private function buildDanSimpanPertanggungjawabanData(Request $request): array
    {
        $validated = $this->validatePeriode($request);

        // Rentang PERSIS SAMA yang sudah pernah tercatat -> reuse, jangan bikin
        // baris baru, jangan error.
        $existing = PertanggungjawabanPeriode::where('tanggal_awal', $validated['tanggal_awal'])
            ->where('tanggal_akhir', $validated['tanggal_akhir'])
            ->first();

        if ($existing) {
            $bulanLabel = $existing->bulan_label;
        } else {
            // Rentang BEDA tapi tumpang tindih sama periode lain -> tetap ditolak.
            $overlap = PertanggungjawabanPeriode::where('tanggal_awal', '<=', $validated['tanggal_akhir'])
                ->where('tanggal_akhir', '>=', $validated['tanggal_awal'])
                ->exists();

            abort_if($overlap, 422, 'Rentang tanggal ini tumpang tindih dengan periode lain yang sudah pernah di-export. Cek tab Riwayat.');

            $bulanLabel = $this->formatBulanLabel($validated['tanggal_awal'], $validated['tanggal_akhir']);

            PertanggungjawabanPeriode::create([
                'bulan_label'   => $bulanLabel,
                'tanggal_awal'  => $validated['tanggal_awal'],
                'tanggal_akhir' => $validated['tanggal_akhir'],
            ]);
        }

        $weeks                = $this->buildWeeksForRange($validated['tanggal_awal'], $validated['tanggal_akhir']);
        $keterangan           = $this->buildKeterangan($weeks);
        $penandatangan        = $this->getPenandatanganLaporan();
        $keteranganBulanLabel = $this->formatBulanSajaLabel($validated['tanggal_awal'], $validated['tanggal_akhir']);

        return [
            'bulanLabel'           => $bulanLabel,
            'keteranganBulanLabel' => $keteranganBulanLabel,
            'tanggalAwal'          => $validated['tanggal_awal'],
            'tanggalAkhir'         => $validated['tanggal_akhir'],
            'weeks'                => $weeks,
            'keterangan'           => $keterangan,
            'penandatangan'        => $penandatangan,
        ];
    }
}