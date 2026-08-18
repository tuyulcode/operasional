<?php

namespace App\Http\Controllers;

use App\Exports\PemakaianBbmExport;
use App\Models\HargaBbm;
use App\Models\Kendaraan;
use App\Models\PemakaianBbm;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PemakaianBbmController extends Controller
{
    /**
     * List transaksi pemakaian BBM (input harian)
     */
    public function index(Request $request)
    {
        $pemakaianBbms = PemakaianBbm::with(['kendaraan', 'hargaBbm', 'pencatat'])
            ->orderByDesc('tanggal')
            ->paginate(20);

        $kendaraans = Kendaraan::orderBy('plat_nomor')->get();

        $edit = null;
        if ($request->has('edit')) {
            $edit = PemakaianBbm::with('hargaBbm')->find($request->query('edit'));
        }

        return view('pemakaian-bbm.index', compact('pemakaianBbms', 'kendaraans', 'edit'));
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
     * Halaman rekap periode (preview di layar)
     */
    public function rekap(Request $request)
    {
        if (!$request->filled('tanggal_awal') || !$request->filled('tanggal_akhir')) {
            return view('pemakaian-bbm.rekap', [
                'groups' => [], 'grandTotal' => null, 'periodeLabel' => null,
                'tanggal_awal' => null, 'tanggal_akhir' => null,
            ]);
        }

        $validated = $this->validatePeriode($request);
        $data = $this->buildRekapData($validated['tanggal_awal'], $validated['tanggal_akhir']);
        $data['tanggal_awal'] = $validated['tanggal_awal'];
        $data['tanggal_akhir'] = $validated['tanggal_akhir'];

        return view('pemakaian-bbm.rekap', $data);
    }

    public function exportExcel(Request $request)
    {
        $validated = $this->validatePeriode($request);
        $data = $this->buildRekapData($validated['tanggal_awal'], $validated['tanggal_akhir']);

        $filename = 'pemakaian-bbm_' . $validated['tanggal_awal'] . '_sd_' . $validated['tanggal_akhir'] . '.xlsx';

        return Excel::download(new PemakaianBbmExport($data), $filename);
    }

    public function exportPdf(Request $request)
    {
        $validated = $this->validatePeriode($request);
        $data = $this->buildRekapData($validated['tanggal_awal'], $validated['tanggal_akhir']);

        $filename = 'pemakaian-bbm_' . $validated['tanggal_awal'] . '_sd_' . $validated['tanggal_akhir'] . '.pdf';

        $pdf = Pdf::loadView('pemakaian-bbm.rekap-pdf', $data)->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    /* =======================================================
     * Helper privat
     * ======================================================= */

    private function validatePemakaian(Request $request): array
    {
        return $request->validate([
            'tanggal'           => 'required|date',
            'kendaraan_id'      => 'required|exists:kendaraan,id',
            'jenis_bbm'         => 'required|in:bensin,solar',
            'liter_paiton'      => 'nullable|numeric|min:0',
            'liter_luar_paiton' => 'nullable|numeric|min:0',
            'service_oli'       => 'nullable|numeric|min:0',
            'jasa'              => 'nullable|numeric|min:0',
        ], [
            'kendaraan_id.required' => 'Kendaraan wajib dipilih.',
            'kendaraan_id.exists'   => 'Kendaraan tidak valid.',
            'jenis_bbm.required'    => 'Jenis BBM wajib dipilih.',
        ]);
    }

    private function buildPayload(array $validated, HargaBbm $hargaBbm): array
    {
        $literPaiton     = (float) ($validated['liter_paiton'] ?? 0);
        $literLuarPaiton = (float) ($validated['liter_luar_paiton'] ?? 0);
        $serviceOli      = (float) ($validated['service_oli'] ?? 0);
        $jasa            = (float) ($validated['jasa'] ?? 0);

        $rpPaiton     = $literPaiton * $hargaBbm->harga_paiton;
        $rpLuarPaiton = $literLuarPaiton * $hargaBbm->harga_luar_paiton;
        $jumlah       = $rpPaiton + $rpLuarPaiton + $serviceOli + $jasa;

        return [
            'kendaraan_id'      => $validated['kendaraan_id'],
            'harga_bbm_id'      => $hargaBbm->id,
            'tanggal'           => $validated['tanggal'],
            'liter_paiton'      => $literPaiton,
            'rp_paiton'         => $rpPaiton,
            'liter_luar_paiton' => $literLuarPaiton,
            'rp_luar_paiton'    => $rpLuarPaiton,
            'service_oli'       => $serviceOli,
            'jasa'              => $jasa,
            'jumlah'            => $jumlah,
            'dicatat_oleh'      => auth()->id(),
        ];
    }

    private function validatePeriode(Request $request): array
    {
        return $request->validate([
            'tanggal_awal'  => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
        ]);
    }

    /**
     * Bangun data rekap: A. Roda Empat, B. Roda Tiga, C. Roda Dua
     * masing-masing di-subgroup per "unit" kalau ada.
     */
    private function buildRekapData(string $tanggalAwal, string $tanggalAkhir): array
    {
        $urutanJenis = [
            'Roda 4' => 'A. Roda Empat',
            'Roda 3' => 'B. Roda Tiga',
            'Roda 2' => 'C. Roda Dua',
        ];

        $kendaraans = Kendaraan::whereIn('nama_jenis', array_keys($urutanJenis))
            ->with(['pemakaianBbm' => function ($q) use ($tanggalAwal, $tanggalAkhir) {
                $q->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
            }])
            ->orderBy('unit')
            ->orderBy('plat_nomor')
            ->get();

        $groups = [];
        $grandTotal = $this->emptyTotal();

        foreach ($urutanJenis as $namaJenis => $labelGroup) {
            $items = $kendaraans->where('nama_jenis', $namaJenis);

            if ($items->isEmpty()) {
                continue;
            }

            $sections = [];
            $no = 1;
            $groupTotal = $this->emptyTotal();

            foreach ($items->groupBy(fn ($k) => $k->unit ?: '') as $unitLabel => $unitItems) {
                $rows = [];

                foreach ($unitItems as $kendaraan) {
                    $sum = [
                        'liter_paiton'      => (float) $kendaraan->pemakaianBbm->sum('liter_paiton'),
                        'rp_paiton'         => (float) $kendaraan->pemakaianBbm->sum('rp_paiton'),
                        'liter_luar_paiton' => (float) $kendaraan->pemakaianBbm->sum('liter_luar_paiton'),
                        'rp_luar_paiton'    => (float) $kendaraan->pemakaianBbm->sum('rp_luar_paiton'),
                        'service_oli'       => (float) $kendaraan->pemakaianBbm->sum('service_oli'),
                        'jasa'              => (float) $kendaraan->pemakaianBbm->sum('jasa'),
                        'jumlah'            => (float) $kendaraan->pemakaianBbm->sum('jumlah'),
                    ];

                    $rows[] = ['no' => $no++, 'plat_nomor' => $kendaraan->plat_nomor] + $sum;

                    foreach ($sum as $key => $val) {
                        $groupTotal[$key] += $val;
                        $grandTotal[$key] += $val;
                    }
                }

                $sections[] = ['label' => $unitLabel, 'rows' => $rows];
            }

            $groups[] = [
                'label'    => $labelGroup,
                'sections' => $sections,
                'total'    => $groupTotal,
            ];
        }

        return [
            'groups'       => $groups,
            'grandTotal'   => $grandTotal,
            'periodeLabel' => Carbon::parse($tanggalAwal)->translatedFormat('d F Y')
                . ' - ' . Carbon::parse($tanggalAkhir)->translatedFormat('d F Y'),
        ];
    }

    private function emptyTotal(): array
    {
        return [
            'liter_paiton' => 0, 'rp_paiton' => 0,
            'liter_luar_paiton' => 0, 'rp_luar_paiton' => 0,
            'service_oli' => 0, 'jasa' => 0, 'jumlah' => 0,
        ];
    }
}