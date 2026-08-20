<?php

namespace App\Services;

use App\Models\Kendaraan;
use Carbon\Carbon;

class PemakaianBbmRekapService
{
    /**
     * Bangun data rekap: A. Roda Empat, B. Roda Tiga, C. Roda Dua
     * masing-masing di-subgroup per "unit" kalau ada.
     */
    public function build(string $tanggalAwal, string $tanggalAkhir): array
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
                        'liter'       => (float) $kendaraan->pemakaianBbm->sum('liter'),
                        'rp'          => (float) $kendaraan->pemakaianBbm->sum('rp'),
                        'service_oli' => (float) $kendaraan->pemakaianBbm->sum('service_oli'),
                        'jasa'        => (float) $kendaraan->pemakaianBbm->sum('jasa'),
                        'jumlah'      => (float) $kendaraan->pemakaianBbm->sum('jumlah'),
                    ];

                    // Kasih keterangan (Luar Paiton) kalau ada transaksi di periode ini yang lokasinya luar Paiton
                    $adaLuarPaiton = $kendaraan->pemakaianBbm->contains('lokasi_pembelian', 'luar_paiton');
                    $platNomor = $kendaraan->plat_nomor . ($adaLuarPaiton ? ' (Luar Paiton)' : '');

                    $rows[] = ['no' => $no++, 'plat_nomor' => $platNomor] + $sum;

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
            'liter' => 0, 'rp' => 0,
            'service_oli' => 0, 'jasa' => 0, 'jumlah' => 0,
        ];
    }
}