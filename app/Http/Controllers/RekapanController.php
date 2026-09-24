<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Penandatangan;
use App\Models\Ppn;
use App\Models\TagihanAir;
use App\Services\RekapanExcel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RekapanController extends Controller
{
    public function buildReport(Request $request)
    {
        $bulan = $request->query('bulan');
        $areaId = $request->query('area_id');

        $year = $month = null;
        if ($bulan && preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            [$year, $month] = array_map('intval', explode('-', $bulan));
        }

        $tagihans = TagihanAir::with('titikMeter.area')
            ->when($year, fn ($q) => $q->whereYear('periode', $year)->whereMonth('periode', $month))
            ->get()
            ->keyBy('titik_meter_id');

        $areaQuery = Area::with(['titikMeter' => fn ($q) => $q->orderBy('nama')])->orderBy('nama');
        if ($areaId) {
            $areaQuery->where('id', $areaId);
        }

        // PENTING: PPN untuk SEMUA rekapan (PDF, Excel, tab Rekapan) selalu
        // memakai PPN yang sedang berstatus "aktif" di Master Data PPN saat
        // laporan ini dibuat -- BUKAN ppn_persentase/ppn_nominal yang sudah
        // dibekukan di baris tagihan_air saat data itu pertama kali diinput.
        // Jadi begitu PPN aktif diubah, semua rekapan (termasuk untuk data
        // lama) otomatis ikut berubah tanpa perlu edit ulang satu-satu.
        $persenPpnAktif = (float) (Ppn::where('status', 'aktif')->value('persentase') ?? 0);

        $data = $areaQuery->get()->map(function ($area) use ($tagihans, $persenPpnAktif) {
            $kenaPpn = (bool) $area->kena_ppn;
            $persenPpn = $kenaPpn ? $persenPpnAktif : 0;

            $rows = $area->titikMeter->map(function ($tm) use ($tagihans, $persenPpn) {
                $tagihan = $tagihans->get($tm->id);

                // Jumlah sebelum PPN dihitung ulang dari pemakaian x tarif
                // (dua-duanya TIDAK terpengaruh PPN), lalu PPN-nya dihitung
                // pakai persentase aktif saat ini.
                $jumlahSebelumPpn = $tagihan ? (float) $tagihan->pemakaian * (float) $tagihan->tarif : 0;
                $ppnNominal = round($jumlahSebelumPpn * $persenPpn / 100, 2);

                return [
                    'titik_meter' => $tm,
                    'tagihan' => $tagihan,
                    'jumlah_sebelum_ppn' => $jumlahSebelumPpn,
                    'ppn_nominal' => $ppnNominal,
                    // Dipakai di kolom "JUMLAH Rp" pada PDF & Excel, menggantikan
                    // $tagihan->jumlah yang nilainya beku dari saat input.
                    'jumlah_dinamis' => $jumlahSebelumPpn + $ppnNominal,
                ];
            });

            // Subtotal/PPN/Total hanya dihitung dari titik meter yang statusnya
            // "aktif" -- harus sama persis dengan baris yang benar-benar
            // ditampilkan di tabel PDF/Excel (yang juga difilter aktif saja),
            // supaya angka Jumlah Total tidak lebih besar dari yang terlihat.
            $rowsAktif = $rows->filter(fn ($r) => ($r['titik_meter']->status ?? 'aktif') === 'aktif');

            $subtotal = $rowsAktif->sum('jumlah_sebelum_ppn');
            $ppn = $rowsAktif->sum('ppn_nominal');

            return [
                'area' => $area,
                'rows' => $rows,
                'jml_titik' => $area->titikMeter->where('status', 'aktif')->count(),
                'subtotal' => $subtotal,
                'total_pemakaian' => $rowsAktif->sum(fn ($r) => $r['tagihan']->pemakaian ?? 0),
                'kena_ppn' => $kenaPpn,
                'persen_ppn' => $persenPpn,
                'ppn' => $ppn,
                'total' => $subtotal + $ppn,
            ];
        });

        return [
            'bulan' => $bulan,
            'areaId' => $areaId,
            'areas' => Area::orderBy('nama')->get(),
            'periodeLabel' => $year ? Carbon::parse("{$year}-{$month}-01")->locale('id')->translatedFormat('F Y') : '',
            'data' => $data,
            'grandTotal' => $data->sum('total'),
            'grandPemakaian' => $data->sum('total_pemakaian'),
            'penandatangan' => Penandatangan::orderBy('id')->get(),
        ];
    }

    public function index(Request $request)
    {
        return redirect()->route('tagihan-air.index', ['tab' => 'rekapan']);
    }

    public function exportExcel(Request $request)
    {
        $report = $this->buildReport($request);

        if (! $report['bulan']) {
            return redirect()->route('rekapan.index')
                ->with('error', 'Pilih bulan dan tahun terlebih dahulu untuk export.');
        }

        $filename = 'rekapan_air_'.$report['bulan'].'.xlsx';

        $writer = new Xlsx(RekapanExcel::generate($report));

        return response()->streamDownload(
            fn () => $writer->save('php://output'),
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    public function exportPdf(Request $request)
    {
        $report = $this->buildReport($request);

        if (! $report['bulan']) {
            return redirect()->route('rekapan.index')
                ->with('error', 'Pilih bulan dan tahun terlebih dahulu untuk export.');
        }

        $filename = 'rekapan_air_'.$report['bulan'].'.pdf';

        $pdf = Pdf::loadView('exports.rekapan-pdf', $report);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream($filename);
    }
}