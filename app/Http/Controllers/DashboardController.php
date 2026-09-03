<?php

namespace App\Http\Controllers;

use App\Models\HargaBbm;
use App\Models\Kendaraan;
use App\Models\PemakaianBbm;
use App\Models\PemakaianEtoll;
use App\Models\TagihanAir;
use App\Models\TitikMeter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        $periodeIni  = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $periodeLalu = $periodeIni->copy()->subMonth();
        $awalBulanIni  = $periodeIni->copy()->startOfMonth();
        $akhirBulanIni = $periodeIni->copy()->endOfMonth();
        $awalBulanLalu  = $periodeLalu->copy()->startOfMonth();
        $akhirBulanLalu = $periodeLalu->copy()->endOfMonth();

        $bulanList = collect(range(1, 12))->map(function ($m) {
            return [
                'value' => $m,
                'label' => Carbon::create()->month($m)->translatedFormat('F'),
            ];
        });

        $tahunAwal = (int) (TagihanAir::min('periode') ? Carbon::parse(TagihanAir::min('periode'))->year : now()->year);
        $tahunList = collect(range(min($tahunAwal, now()->year - 1), now()->year))->reverse()->values();

        // --- BULAN INI: 3 queries ---
        $totalEtollBulanIni = (float) PemakaianEtoll::whereBetween('tanggal', [$awalBulanIni, $akhirBulanIni])->sum('nominal');

        $bbmBulanIni = PemakaianBbm::whereBetween('tanggal', [$awalBulanIni, $akhirBulanIni])
            ->selectRaw('COALESCE(SUM(jumlah),0) as total_rp, COALESCE(SUM(liter),0) as total_liter')
            ->first();
        $totalBbmBulanIni     = (float) $bbmBulanIni->total_rp;
        $totalLiterBbmBulanIni = (float) $bbmBulanIni->total_liter;

        $totalAirBulanIni = (float) TagihanAir::whereBetween('periode', [$awalBulanIni, $akhirBulanIni])->sum('jumlah');
        $totalOperasionalBulanIni = $totalEtollBulanIni + $totalBbmBulanIni + $totalAirBulanIni;

        // --- BULAN LALU: 3 queries ---
        $totalEtollLalu = (float) PemakaianEtoll::whereBetween('tanggal', [$awalBulanLalu, $akhirBulanLalu])->sum('nominal');
        $totalBbmLalu   = (float) PemakaianBbm::whereBetween('tanggal', [$awalBulanLalu, $akhirBulanLalu])->sum('jumlah');
        $totalAirLalu   = (float) TagihanAir::whereBetween('periode', [$awalBulanLalu, $akhirBulanLalu])->sum('jumlah');
        $totalOperasionalBulanLalu = $totalEtollLalu + $totalBbmLalu + $totalAirLalu;

        $persenPerubahan = $totalOperasionalBulanLalu > 0
            ? round((($totalOperasionalBulanIni - $totalOperasionalBulanLalu) / $totalOperasionalBulanLalu) * 100, 1)
            : null;

        $jumlahKendaraan = Kendaraan::count();

        $komposisiBiaya = [
            'labels' => ['E-Toll', 'BBM', 'Tagihan Air'],
            'data'   => [$totalEtollBulanIni, $totalBbmBulanIni, $totalAirBulanIni],
        ];

        $pemakaianBbmPerKendaraan = DB::table('pemakaian_bbm')
            ->join('kendaraan', 'kendaraan.id', '=', 'pemakaian_bbm.kendaraan_id')
            ->whereBetween('pemakaian_bbm.tanggal', [$awalBulanIni, $akhirBulanIni])
            ->select(
                'kendaraan.id',
                'kendaraan.plat_nomor',
                'kendaraan.nama_jenis',
                'kendaraan.unit',
                DB::raw('SUM(pemakaian_bbm.liter) as total_liter'),
                DB::raw('SUM(pemakaian_bbm.jumlah) as total_rp')
            )
            ->groupBy('kendaraan.id', 'kendaraan.plat_nomor', 'kendaraan.nama_jenis', 'kendaraan.unit')
            ->orderByDesc('total_rp')
            ->get();

        $pemakaianAirPerTitikMeter = DB::table('tagihan_air')
            ->join('titik_meter', 'titik_meter.id', '=', 'tagihan_air.titik_meter_id')
            ->whereBetween('tagihan_air.periode', [$awalBulanIni, $akhirBulanIni])
            ->select(
                'titik_meter.id',
                'titik_meter.nama',
                'titik_meter.lokasi_flow_meter',
                DB::raw('SUM(tagihan_air.pemakaian) as total_m3'),
                DB::raw('SUM(tagihan_air.jumlah) as total_rp')
            )
            ->groupBy('titik_meter.id', 'titik_meter.nama', 'titik_meter.lokasi_flow_meter')
            ->orderByDesc('total_rp')
            ->get();

        $pemakaianEtollPerPemegang = DB::table('pemakaian_etoll')
            ->join('pemegang_kendaraan', 'pemegang_kendaraan.id', '=', 'pemakaian_etoll.pemegang_kendaraan_id')
            ->whereBetween('pemakaian_etoll.tanggal', [$awalBulanIni, $akhirBulanIni])
            ->select(
                'pemegang_kendaraan.id',
                'pemegang_kendaraan.nama',
                DB::raw('COUNT(pemakaian_etoll.id) as total_transaksi'),
                DB::raw('SUM(pemakaian_etoll.nominal) as total_rp')
            )
            ->groupBy('pemegang_kendaraan.id', 'pemegang_kendaraan.nama')
            ->orderByDesc('total_rp')
            ->get();

        $hargaBbmTerbaru = HargaBbm::orderByDesc('tanggal_berlaku')->first();

        // --- TREN 6 BULAN: 3 grouped queries (bukan 18) ---
        $jumlahBulanTren = 6;
        $awalTren = $periodeIni->copy()->subMonths($jumlahBulanTren - 1)->startOfMonth();
        $akhirTren = $periodeIni->copy()->endOfMonth();

        $etollPerBulan = PemakaianEtoll::whereBetween('tanggal', [$awalTren, $akhirTren])
            ->selectRaw("DATE_FORMAT(tanggal, '%Y-%m') as bulan, SUM(nominal) as total")
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        $bbmPerBulan = PemakaianBbm::whereBetween('tanggal', [$awalTren, $akhirTren])
            ->selectRaw("DATE_FORMAT(tanggal, '%Y-%m') as bulan, SUM(jumlah) as total")
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        $airPerBulan = TagihanAir::whereBetween('periode', [$awalTren, $akhirTren])
            ->selectRaw("DATE_FORMAT(periode, '%Y-%m') as bulan, SUM(jumlah) as total")
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        $trenBulanan = collect();
        for ($i = $jumlahBulanTren - 1; $i >= 0; $i--) {
            $p = $periodeIni->copy()->subMonths($i);
            $key = $p->format('Y-m');
            $etoll = (float) ($etollPerBulan[$key] ?? 0);
            $bbm   = (float) ($bbmPerBulan[$key] ?? 0);
            $air   = (float) ($airPerBulan[$key] ?? 0);

            $trenBulanan->push([
                'label' => $p->translatedFormat('M Y'),
                'etoll' => $etoll,
                'bbm'   => $bbm,
                'air'   => $air,
                'total' => $etoll + $bbm + $air,
            ]);
        }

        $trenBiaya = [
            'labels' => $trenBulanan->pluck('label'),
            'total'  => $trenBulanan->pluck('total'),
            'bbm'    => $trenBulanan->pluck('bbm'),
            'etoll'  => $trenBulanan->pluck('etoll'),
            'air'    => $trenBulanan->pluck('air'),
        ];

        // --- ALERTS ---
        $alerts = [];

        $kendaraanIdSudahIsi = DB::table('pemakaian_bbm')
            ->whereBetween('tanggal', [$awalBulanIni, $akhirBulanIni])
            ->pluck('kendaraan_id')
            ->unique();

        $jumlahKendaraanBelumIsiBbm = Kendaraan::whereNotIn('id', $kendaraanIdSudahIsi)->count();

        if ($jumlahKendaraanBelumIsiBbm > 0) {
            $alerts[] = [
                'type'  => 'warning',
                'icon'  => 'fa-gas-pump',
                'text'  => "{$jumlahKendaraanBelumIsiBbm} kendaraan belum tercatat pengisian BBM di periode {$periodeIni->translatedFormat('F Y')}.",
            ];
        }

        $titikMeterIdSudahDicatat = DB::table('tagihan_air')
            ->whereBetween('periode', [$awalBulanIni, $akhirBulanIni])
            ->pluck('titik_meter_id')
            ->unique();

        $titikMeterBelumDicatat = TitikMeter::where('status', 'aktif')
            ->whereNotIn('id', $titikMeterIdSudahDicatat)
            ->pluck('nama');

        if ($titikMeterBelumDicatat->isNotEmpty()) {
            $alerts[] = [
                'type'  => 'danger',
                'icon'  => 'fa-droplet',
                'text'  => 'Titik meter belum dicatat bulan ini: ' . $titikMeterBelumDicatat->implode(', ') . '.',
            ];
        }

        if ($hargaBbmTerbaru) {
            $hariSejakUpdate = Carbon::parse($hargaBbmTerbaru->tanggal_berlaku)->diffInDays(now());
            if ($hariSejakUpdate > 30) {
                $alerts[] = [
                    'type' => 'warning',
                    'icon' => 'fa-tag',
                    'text' => "Harga BBM belum diperbarui selama {$hariSejakUpdate} hari (terakhir {$hargaBbmTerbaru->tanggal_berlaku}).",
                ];
            }
        } else {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'fa-tag',
                'text' => 'Belum ada data harga BBM yang tercatat.',
            ];
        }

        if ($persenPerubahan !== null && $persenPerubahan >= 20) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'fa-arrow-trend-up',
                'text' => "Biaya operasional naik {$persenPerubahan}% dibanding bulan sebelumnya.",
            ];
        }

        if (empty($alerts)) {
            $alerts[] = [
                'type' => 'success',
                'icon' => 'fa-circle-check',
                'text' => 'Tidak ada catatan khusus. Semua data operasional bulan ini lengkap.',
            ];
        }

        return view('dashboard.index', compact(
            'bulan',
            'tahun',
            'bulanList',
            'tahunList',
            'periodeIni',
            'totalEtollBulanIni',
            'totalBbmBulanIni',
            'totalLiterBbmBulanIni',
            'totalAirBulanIni',
            'totalOperasionalBulanIni',
            'persenPerubahan',
            'jumlahKendaraan',
            'komposisiBiaya',
            'pemakaianBbmPerKendaraan',
            'pemakaianAirPerTitikMeter',
            'pemakaianEtollPerPemegang',
            'hargaBbmTerbaru',
            'trenBiaya',
            'alerts',
        ));
    }
}