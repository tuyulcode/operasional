<?php

namespace Database\Seeders;

use App\Models\JenisKendaraan;
use App\Models\Kendaraan;
use Illuminate\Database\Seeder;

class KendaraanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['merek' => 'Honda',      'plat_nomor' => 'B 1234 ABC', 'nama_jenis' => 'Roda 2'],
            ['merek' => 'Suzuki',     'plat_nomor' => 'B 4567 DEF', 'nama_jenis' => 'Roda 3'],
            ['merek' => 'Toyota',     'plat_nomor' => 'B 3456 CDE', 'nama_jenis' => 'Roda 4'],
        ];

        foreach ($data as $item) {
            $jenisKendaraan = JenisKendaraan::where('nama_merek', $item['merek'])->first();

            // Lewati jika mereknya belum ada di tabel jenis_kendaraan
            if (! $jenisKendaraan) {
                continue;
            }

            Kendaraan::firstOrCreate([
                'plat_nomor' => $item['plat_nomor'],
            ], [
                'jenis_kendaraan_id' => $jenisKendaraan->id,
                'nama_jenis'         => $item['nama_jenis'],
            ]);
        }
    }
}