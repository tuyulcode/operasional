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
        // 1. Benerin dulu data lama: isi ulang tanggal_berlaku pakai tanggal dari
        //    created_at masing-masing baris, supaya tidak ada tanggal yang sama
        //    persis
        DB::statement('UPDATE harga_bbm SET tanggal_berlaku = DATE(created_at)');

        // 2. Kunci kolomnya jadi UNIQUE
        Schema::table('harga_bbm', function (Blueprint $table) {
            $table->unique('tanggal_berlaku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('harga_bbm', function (Blueprint $table) {
            $table->dropUnique(['tanggal_berlaku']);
        });
    }
};