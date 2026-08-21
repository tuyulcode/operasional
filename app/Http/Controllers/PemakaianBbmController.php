<?php

namespace App\Http\Controllers;

use App\Exports\PemakaianBbmExport;
use App\Exports\PertanggungjawabanExport;
use App\Models\HargaBbm;
use App\Models\Kendaraan;
use App\Models\PemakaianBbm;
use App\Models\Penandatangan;
use App\Services\PemakaianBbmRekapService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
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

        // Map jenis BBM -> harga per liter, dipakai JS buat nampilin harga otomatis (read-only)
        $hargaBbmMap = HargaBbm::pluck('harga_paiton', 'jenis');

        $edit = null;
        if ($request->has('edit')) {
            $edit = PemakaianBbm::with('hargaBbm')->find($request->query('edit'));
        }

        return view('pemakaian-bbm.index', compact('pemakaianBbms', 'kendaraans', 'edit', 'hargaBbmMap'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePemakaian($request);

        $hargaBbm = HargaBbm::where('jenis', $validated['jenis_bbm'])->firstOrFail();

        PemakaianBbm::create($this->buildPayload($validated, $hargaBbm));

        return redirect()->route('pemakaian-bbm.index')
            ->with('success', 'Data pemakaian BBM berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $pemakaian = PemakaianBbm::findOrFail($id);

        $validated = $this->validatePemakaian($request);

        $hargaBbm = HargaBbm::where('jenis', $validated['jenis_bbm'])->firstOrFail();

        $pemakaian->update($this->buildPayload($validated, $hargaBbm));

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
     * Halaman laporan Pertanggungjawaban: gabungan beberapa minggu dalam 1 bulan,
     * dengan tabel sederhana (No, Nomor Kendaraan, Liter, Rp) + keterangan + tanda tangan.
     */
    public function pertanggungjawaban(Request $request)
    {
        $bulanLabel    = $request->query('bulan_label');
        $minggus       = $request->query('minggu', []);
        $weeks         = [];
        $keterangan    = null;
        $penandatangan = Penandatangan::where('jabatan', Penandatangan::ASMAN)->first();

        if ($bulanLabel && !empty($minggus)) {
            $validated  = $this->validatePertanggungjawaban($request);
            $bulanLabel = $validated['bulan_label'];
            $minggus    = $validated['minggu'];

            $weeks      = $this->buildWeeks($minggus);
            $keterangan = $this->buildKeterangan($minggus);
        }

        return view('pemakaian-bbm.pertanggungjawaban', compact(
            'bulanLabel', 'minggus', 'weeks', 'keterangan', 'penandatangan'
        ));
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

    /* =======================================================
     * Helper privat
     * ======================================================= */

    private function validatePemakaian(Request $request): array
    {
        return $request->validate([
            'tanggal'          => 'required|date',
            'kendaraan_id'     => 'required|exists:kendaraan,id',
            'jenis_bbm'        => 'required|in:bensin,solar',
            'lokasi_pembelian' => 'required|in:paiton,luar_paiton',
            'liter'            => 'nullable|numeric|min:0',
            'service_oli'      => 'nullable|numeric|min:0',
            'jasa'             => 'nullable|numeric|min:0',
        ], [
            'kendaraan_id.required'     => 'Kendaraan wajib dipilih.',
            'kendaraan_id.exists'       => 'Kendaraan tidak valid.',
            'jenis_bbm.required'        => 'Jenis BBM wajib dipilih.',
            'lokasi_pembelian.required' => 'Lokasi pembelian wajib dipilih.',
        ]);
    }

    private function buildPayload(array $validated, HargaBbm $hargaBbm): array
    {
        $liter      = (float) ($validated['liter'] ?? 0);
        $serviceOli = (float) ($validated['service_oli'] ?? 0);
        $jasa       = (float) ($validated['jasa'] ?? 0);

        $rp     = $liter * $hargaBbm->harga_paiton;
        $jumlah = $rp + $serviceOli + $jasa;

        return [
            'kendaraan_id'     => $validated['kendaraan_id'],
            'harga_bbm_id'     => $hargaBbm->id,
            'tanggal'          => $validated['tanggal'],
            'lokasi_pembelian' => $validated['lokasi_pembelian'],
            'liter'            => $liter,
            'rp'               => $rp,
            'service_oli'      => $serviceOli,
            'jasa'             => $jasa,
            'jumlah'           => $jumlah,
            'dicatat_oleh'     => auth()->id(),
        ];
    }

    private function validatePeriode(Request $request): array
    {
        return $request->validate([
            'tanggal_awal'  => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
        ]);
    }

    private function validatePertanggungjawaban(Request $request): array
    {
        return $request->validate([
            'bulan_label'             => 'required|string|max:50',
            'minggu'                  => 'required|array|min:1',
            'minggu.*.tanggal_awal'   => 'required|date',
            'minggu.*.tanggal_akhir'  => 'required|date|after_or_equal:minggu.*.tanggal_awal',
        ]);
    }

    /**
     * Bangun data tiap minggu (grouped table) memakai rekap service yang sudah ada.
     */
    private function buildWeeks(array $minggus): array
    {
        $weeks = [];

        foreach ($minggus as $i => $minggu) {
            $data = $this->rekapService->build($minggu['tanggal_awal'], $minggu['tanggal_akhir']);

            $weeks[] = [
                'no'           => $i + 1,
                'periodeLabel' => $data['periodeLabel'],
                'groups'       => $data['groups'],
                'grandTotal'   => $data['grandTotal'],
            ];
        }

        return $weeks;
    }

    /**
     * Hitung total keterangan (Paiton / Luar Paiton / Service-Oli / Jasa) untuk
     * seluruh rentang minggu yang dipilih, dipakai di bagian "Keterangan" laporan.
     */
    private function buildKeterangan(array $minggus): array
    {
        $rows = PemakaianBbm::query()
            ->where(function ($query) use ($minggus) {
                foreach ($minggus as $minggu) {
                    $query->orWhereBetween('tanggal', [$minggu['tanggal_awal'], $minggu['tanggal_akhir']]);
                }
            })
            ->get();

        $paiton     = (float) $rows->where('lokasi_pembelian', 'paiton')->sum('rp');
        $luarPaiton = (float) $rows->where('lokasi_pembelian', 'luar_paiton')->sum('rp');
        $serviceOli = (float) $rows->sum('service_oli');
        $jasa       = (float) $rows->sum('jasa');

        return [
            'paiton'      => $paiton,
            'luar_paiton' => $luarPaiton,
            'service_oli' => $serviceOli,
            'jasa'        => $jasa,
            'jumlah'      => $paiton + $luarPaiton + $serviceOli + $jasa,
        ];
    }

    /**
     * Kumpulkan semua data yang dibutuhkan export Excel/PDF Pertanggungjawaban.
     */
    private function buildPertanggungjawabanData(Request $request): array
    {
        $validated = $this->validatePertanggungjawaban($request);

        $weeks         = $this->buildWeeks($validated['minggu']);
        $keterangan    = $this->buildKeterangan($validated['minggu']);
        $penandatangan = Penandatangan::where('jabatan', Penandatangan::ASMAN)->first();

        return [
            'bulanLabel'    => $validated['bulan_label'],
            'weeks'         => $weeks,
            'keterangan'    => $keterangan,
            'penandatangan' => $penandatangan,
        ];
    }
}