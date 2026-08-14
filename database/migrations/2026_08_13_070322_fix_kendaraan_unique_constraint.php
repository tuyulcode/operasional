<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $indexExists = collect(DB::select("SHOW INDEX FROM kendaraan WHERE Key_name = 'kendaraan_nama_jenis_unique'"))->isNotEmpty();

        if ($indexExists) {
            Schema::table('kendaraan', function (Blueprint $table) {
                $table->dropUnique('kendaraan_nama_jenis_unique');
            });
        }

        // Pastikan plat_nomor unique (kalau ternyata di suatu database belum ada).
        $platNomorUniqueExists = collect(DB::select("SHOW INDEX FROM kendaraan WHERE Key_name = 'kendaraan_plat_nomor_unique'"))->isNotEmpty();

        if (! $platNomorUniqueExists) {
            Schema::table('kendaraan', function (Blueprint $table) {
                $table->unique('plat_nomor', 'kendaraan_plat_nomor_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kendaraan', function (Blueprint $table) {
            $table->unique('nama_jenis', 'kendaraan_nama_jenis_unique');
        });
    }
};