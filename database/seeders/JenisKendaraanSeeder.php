<?php

namespace Database\Seeders;

use App\Models\JenisKendaraan;
use Illuminate\Database\Seeder;

class JenisKendaraanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Honda',
            'Yamaha',
            'Toyota',
            'Suzuki',
            'Mitsubishi',
            'Daihatsu',
            'Kawasaki',
        ];

        foreach ($data as $merek) {
            JenisKendaraan::firstOrCreate([
                'nama_merek' => $merek,
            ]);
        }
    }
}