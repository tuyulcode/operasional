<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $data = [
            [
                'area_id' => 6,
                'nama' => 'PERCIE CONSORTIUM',
                'alamat' => 'PLTU PAITON',
                'lokasi_flow_meter' => 'AREA PLTU',
            ],
            [
                'area_id' => 3,
                'nama' => 'KAMPUS PAITON',
                'alamat' => 'PLTU PAITON',
                'lokasi_flow_meter' => 'Areal PLTU',
            ],
            [
                'area_id' => 5,
                'nama' => 'PT. MARITIM BATUBARA PERTAMA',
                'alamat' => 'PLTU PAITON',
                'lokasi_flow_meter' => 'Areal PLTU',
            ],
            [
                'area_id' => 1,
                'nama' => 'PAITON RESORT HOTEL',
                'alamat' => 'PLTU PAITON',
                'lokasi_flow_meter' => 'PAITON RESORT HOTEL',
            ],
        ];

        foreach ($data as $d) {
            DB::table('area')
                ->where('id', $d['area_id'])
                ->update([
                    'nama' => $d['nama'],
                    'alamat' => $d['alamat'],
                ]);

            DB::table('titik_meter')
                ->where('area_id', $d['area_id'])
                ->update(['lokasi_flow_meter' => $d['lokasi_flow_meter']]);
        }
    }

    public function down(): void
    {
        $data = [
            ['area_id' => 6, 'nama' => 'PC', 'alamat' => 'pltu paiton'],
            ['area_id' => 3, 'nama' => 'Kampus', 'alamat' => 'dakdandjxsacksa'],
            ['area_id' => 5, 'nama' => 'Maritim', 'alamat' => 'area pltu'],
            ['area_id' => 1, 'nama' => 'Hotel', 'alamat' => 'KM 141 ssdfsfs dfsd'],
        ];

        foreach ($data as $d) {
            DB::table('area')
                ->where('id', $d['area_id'])
                ->update([
                    'nama' => $d['nama'],
                    'alamat' => $d['alamat'],
                ]);

            DB::table('titik_meter')
                ->where('area_id', $d['area_id'])
                ->update(['lokasi_flow_meter' => null]);
        }
    }
};
