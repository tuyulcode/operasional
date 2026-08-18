<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('tagihan_air')
            ->whereNotNull('foto')
            ->where('foto', '!=', '')
            ->get(['id', 'foto']);

        foreach ($rows as $row) {
            DB::table('tagihan_air_foto')->updateOrInsert(
                ['tagihan_air_id' => $row->id, 'path_foto' => $row->foto],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        DB::table('tagihan_air_foto')->delete();
    }
};
