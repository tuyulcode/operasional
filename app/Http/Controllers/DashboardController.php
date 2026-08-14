<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use App\Models\PemakaianBbm;
use App\Models\PemakaianEtoll;
use App\Models\TagihanAir;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $bulanIni = Carbon::now()->startOfMonth();
        $bulanLalu = Carbon::now()->subMonth()->startOfMonth();

        // Stat cards
        $totalEtollBulanIni = PemakaianEtoll::whereMonth('tanggal', $bulanIni->month)
            ->whereYear('tanggal', $bulanIni->year)
            ->sum('nominal');

        $totalBbmBulanIni = PemakaianBbm::whereMonth('tanggal', $bulanIni->month)
            ->whereYear('tanggal', $bulanIni->year)
            ->sum('jumlah');

        $totalAirBulanIni = TagihanAir::whereMonth('periode', $bulanIni->month)
            ->whereYear('periode', $bulanIni->year)
            ->sum('jumlah');

        $jumlahKendaraan = Kendaraan::count();

        // Chart data - 6 bulan terakhir
        $chartLabels = [];
        $chartEtoll = [];
        $chartBbm = [];
        $chartAir = [];

        for ($i = 5; $i >= 0; $i--) {
            $bulan = Carbon::now()->subMonths($i);
            $chartLabels[] = $bulan->translatedFormat('M Y');

            $chartEtoll[] = (float) PemakaianEtoll::whereMonth('tanggal', $bulan->month)
                ->whereYear('tanggal', $bulan->year)
                ->sum('nominal');

            $chartBbm[] = (float) PemakaianBbm::whereMonth('tanggal', $bulan->month)
                ->whereYear('tanggal', $bulan->year)
                ->sum('jumlah');

            $chartAir[] = (float) TagihanAir::whereMonth('periode', $bulan->month)
                ->whereYear('periode', $bulan->year)
                ->sum('jumlah');
        }

        // Transaksi terakhir (latest 5)
        $latestEtoll = PemakaianEtoll::with('kendaraan', 'pencatat')
            ->latest('tanggal')
            ->take(5)
            ->get();

        $latestBbm = PemakaianBbm::with('kendaraan', 'pencatat')
            ->latest('tanggal')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'totalEtollBulanIni',
            'totalBbmBulanIni',
            'totalAirBulanIni',
            'jumlahKendaraan',
            'chartLabels',
            'chartEtoll',
            'chartBbm',
            'chartAir',
            'latestEtoll',
            'latestBbm',
        ));
    }
}
