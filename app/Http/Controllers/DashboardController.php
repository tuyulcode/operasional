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
        // ==============================
        // 3. FILTER BULAN / TAHUN
        // ==============================
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        $periodeIni  = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $periodeLalu = $periodeIni->copy()->subMonth();

        // Untuk dropdown filter di view
        $bulanList = collect(range(1, 12))->map(function ($m) {
            return [
                'value' => $m,
                'label' => Carbon::create()->month($m)->translatedFormat('F'),
            ];
        });

        $tahunAwal = (int) (TagihanAir::min('periode') ? Carbon::parse(TagihanAir::min('periode'))->year : now()->year);
        $tahunList = collect(range(min($tahunAwal, now()->year - 1), now()->year))->reverse()->values();

        // ==============================
        // 1. TOTAL BIAYA OPERASIONAL
        // 2. BBM: Rp + Liter
        // ==============================
        $totalEtollBulanIni = (float) PemakaianEtoll::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->sum('nominal');

        $bbmBulanIni = PemakaianBbm::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->selectRaw('COALESCE(SUM(jumlah),0) as total_rp, COALESCE(SUM(liter),0) as total_liter')
            ->first();

        $totalBbmBulanIni     = (float) $bbmBulanIni->total_rp;
        $totalLiterBbmBulanIni = (float) $bbmBulanIni->total_liter;

        $totalAirBulanIni = (float) TagihanAir::whereMonth('periode', $bulan)
            ->whereYear('periode', $tahun)
            ->sum('jumlah');

        $totalOperasionalBulanIni = $totalEtollBulanIni + $totalBbmBulanIni + $totalAirBulanIni;

        // Perbandingan dengan bulan sebelumnya (untuk alert & info)
        $totalOperasionalBulanLalu =
            (float) PemakaianEtoll::whereMonth('tanggal', $periodeLalu->month)->whereYear('tanggal', $periodeLalu->year)->sum('nominal')
            + (float) PemakaianBbm::whereMonth('tanggal', $periodeLalu->month)->whereYear('tanggal', $periodeLalu->year)->sum('jumlah')
            + (float) TagihanAir::whereMonth('periode', $periodeLalu->month)->whereYear('periode', $periodeLalu->year)->sum('jumlah');

        $persenPerubahan = $totalOperasionalBulanLalu > 0
            ? round((($totalOperasionalBulanIni - $totalOperasionalBulanLalu) / $totalOperasionalBulanLalu) * 100, 1)
            : null;

        $jumlahKendaraan = Kendaraan::count();

        // ==============================
        // 4. KOMPOSISI BIAYA (Donut Chart)
        // ==============================
        $komposisiBiaya = [
            'labels' => ['E-Toll', 'BBM', 'Tagihan Air'],
            'data'   => [$totalEtollBulanIni, $totalBbmBulanIni, $totalAirBulanIni],
        ];

        // ==============================
        // 5. PEMAKAIAN BBM PER KENDARAAN
        // ==============================
        $pemakaianBbmPerKendaraan = DB::table('pemakaian_bbm')
            ->join('kendaraan', 'kendaraan.id', '=', 'pemakaian_bbm.kendaraan_id')
            ->whereMonth('pemakaian_bbm.tanggal', $bulan)
            ->whereYear('pemakaian_bbm.tanggal', $tahun)
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

        // ==============================
        // 6. PEMAKAIAN AIR PER TITIK METER
        // ==============================
        $pemakaianAirPerTitikMeter = DB::table('tagihan_air')
            ->join('titik_meter', 'titik_meter.id', '=', 'tagihan_air.titik_meter_id')
            ->whereMonth('tagihan_air.periode', $bulan)
            ->whereYear('tagihan_air.periode', $tahun)
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

        // ==============================
        // 6B. PEMAKAIAN E-TOLL PER PEMEGANG KENDARAAN  (BARU)
        // ==============================
        $pemakaianEtollPerPemegang = DB::table('pemakaian_etoll')
            ->join('pemegang_kendaraan', 'pemegang_kendaraan.id', '=', 'pemakaian_etoll.pemegang_kendaraan_id')
            ->whereMonth('pemakaian_etoll.tanggal', $bulan)
            ->whereYear('pemakaian_etoll.tanggal', $tahun)
            ->select(
                'pemegang_kendaraan.id',
                'pemegang_kendaraan.nama',
                DB::raw('COUNT(pemakaian_etoll.id) as total_transaksi'),
                DB::raw('SUM(pemakaian_etoll.nominal) as total_rp')
            )
            ->groupBy('pemegang_kendaraan.id', 'pemegang_kendaraan.nama')
            ->orderByDesc('total_rp')
            ->get();

        // ==============================
        // 7. HARGA BBM TERBARU
        // ==============================
        $hargaBbmTerbaru = HargaBbm::orderByDesc('tanggal_berlaku')->first();

        // ==============================
        // 9. TREN BIAYA OPERASIONAL (6 BULAN TERAKHIR)  (BARU)
        // ==============================
        $jumlahBulanTren = 6;
        $trenBulanan = collect();

        for ($i = $jumlahBulanTren - 1; $i >= 0; $i--) {
            $p = $periodeIni->copy()->subMonths($i);

            $etoll = (float) PemakaianEtoll::whereMonth('tanggal', $p->month)->whereYear('tanggal', $p->year)->sum('nominal');
            $bbm   = (float) PemakaianBbm::whereMonth('tanggal', $p->month)->whereYear('tanggal', $p->year)->sum('jumlah');
            $air   = (float) TagihanAir::whereMonth('periode', $p->month)->whereYear('periode', $p->year)->sum('jumlah');

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

        // ==============================
        // 8. ALERT / INFORMASI OPERASIONAL
        // ==============================
        $alerts = [];

        // a. Kendaraan yang belum mengisi BBM di periode ini
        $kendaraanIdSudahIsi = DB::table('pemakaian_bbm')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
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

        // b. Titik meter aktif yang belum dicatat tagihan airnya di periode ini
        $titikMeterIdSudahDicatat = DB::table('tagihan_air')
            ->whereMonth('periode', $bulan)
            ->whereYear('periode', $tahun)
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

        // c. Harga BBM sudah lama tidak diupdate
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

        // d. Kenaikan biaya operasional signifikan dibanding bulan sebelumnya
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