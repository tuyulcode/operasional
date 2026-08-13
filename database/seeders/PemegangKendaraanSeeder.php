<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PemegangKendaraanSeeder extends Seeder
{
    public function run(): void
    {
        $nama = [
            'SAMPURNO',
            'SUWOKO',
            'HAMID',
            'NICO',
            'BAYU',
            'M. ANDI',
            'ROHIM',
            'SYAMHARI',
            'DONI',
            'AGUS',
            'HENDRAWAN',
            'PUTRA',
            'HASAN',
            'M.OPS 1',
            'M. OPS 2',
            'M. HAR 1',
            'M. HAR 2',
            'M. ENGIN',
            'M. B S',
            'S M',
        ];

        foreach ($nama as $n) {
            DB::table('pemegang_kendaraan')->updateOrInsert(
                ['nama' => $n],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}