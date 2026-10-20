<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TagihanAir;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagihanAirController extends Controller
{
    private const BULAN = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    public function index(Request $request): JsonResponse
    {
        $bulan = (string) $request->input('bulan', '');
        $areaId = $request->input('area_id');
        $search = trim((string) $request->input('search', ''));

        $tagihans = TagihanAir::query()
            ->with(['titikMeter.area', 'fotos'])
            ->when($areaId !== null && $areaId !== '', function (Builder $query) use ($areaId) {
                $query->whereHas('titikMeter', function (Builder $titikMeter) use ($areaId) {
                    $titikMeter->where('area_id', (int) $areaId);
                });
            })
            ->when(preg_match('/^\d{4}-\d{2}$/', $bulan) === 1, function (Builder $query) use ($bulan) {
                [$tahun, $bulanKe] = explode('-', $bulan);

                $query
                    ->whereYear('periode', (int) $tahun)
                    ->whereMonth('periode', (int) $bulanKe);
            })
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->whereHas('titikMeter', function (Builder $titikMeter) use ($search) {
                    $titikMeter->where('nama', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('periode')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $tagihans->map(fn (TagihanAir $tagihan) => $this->format($tagihan))->values(),
        ]);
    }

    private function format(TagihanAir $tagihan): array
    {
        $titikMeter = $tagihan->titikMeter;
        $area = $titikMeter?->area;
        $periode = $tagihan->periode;

        return [
            'id' => (int) $tagihan->id,
            'titik_meter_id' => (int) $tagihan->titik_meter_id,
            'titik_meter_nama' => $titikMeter?->nama ?? '-',
            'area_id' => $titikMeter?->area_id !== null ? (int) $titikMeter->area_id : null,
            'area_nama' => $area?->nama ?? '-',
            'lokasi_flow_meter' => $titikMeter?->lokasi_flow_meter,
            'periode' => $periode?->format('Y-m') ?? '',
            'periode_label' => $periode instanceof Carbon
                ? (self::BULAN[(int) $periode->month] ?? '').' '.$periode->year
                : '',
            'meter_lalu' => (float) $tagihan->meter_lalu,
            'meter_ini' => (float) $tagihan->meter_ini,
            'meter_faktor' => (float) $tagihan->meter_faktor,
            'tarif' => (float) $tagihan->tarif,
            'pemakaian' => (float) $tagihan->pemakaian,
            'ppn_persentase' => (float) ($tagihan->ppn_persentase ?? 0),
            'ppn_nominal' => (float) ($tagihan->ppn_nominal ?? 0),
            'jumlah' => (float) $tagihan->jumlah,
            'fotos' => $tagihan->fotos->map(fn ($foto) => [
                'id' => (int) $foto->id,
                'url' => null,
                'photo_status' => 'not_found',
            ])->values(),
            'created_at' => $tagihan->created_at?->toIso8601String(),
            'updated_at' => $tagihan->updated_at?->toIso8601String(),
        ];
    }
}
