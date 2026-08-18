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
        Carbon::setLocale('id');

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

        $ppnPersen = (float) (Ppn::where('status', 'aktif')->value('persentase') ?? 0);

        $data = $areaQuery->get()->map(function ($area) use ($tagihans, $ppnPersen) {
            $rows = $area->titikMeter->map(fn ($tm) => [
                'titik_meter' => $tm,
                'tagihan' => $tagihans->get($tm->id),
            ]);

            $subtotal = $rows->sum(fn ($r) => $r['tagihan']->jumlah ?? 0);
            $kenaPpn = (bool) $area->kena_ppn;
            $ppn = $kenaPpn ? round($subtotal * $ppnPersen / 100, 2) : 0;

            return [
                'area' => $area,
                'rows' => $rows,
                'jml_titik' => $area->titikMeter->count(),
                'subtotal' => $subtotal,
                'total_pemakaian' => $rows->sum(fn ($r) => $r['tagihan']->pemakaian ?? 0),
                'kena_ppn' => $kenaPpn,
                'persen_ppn' => $ppnPersen,
                'ppn' => $ppn,
                'total' => $subtotal + $ppn,
            ];
        });

        return [
            'bulan' => $bulan,
            'areaId' => $areaId,
            'areas' => Area::orderBy('nama')->get(),
            'periodeLabel' => $year ? Carbon::parse("{$year}-{$month}-01")->translatedFormat('F Y') : '',
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
