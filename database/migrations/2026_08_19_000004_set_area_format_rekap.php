<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $format = [
            2 => 'list',       // Puncak
            4 => 'list',       // Puncak 1
            1 => 'multikolom', // PAITON RESORT HOTEL
        ];

        foreach ($format as $areaId => $rekap) {
            DB::table('area')
                ->where('id', $areaId)
                ->update(['format_rekap' => $rekap]);
        }
    }

    public function down(): void
    {
        DB::table('area')
            ->whereIn('id', [1, 2, 4])
            ->update(['format_rekap' => 'standar']);
    }
};
