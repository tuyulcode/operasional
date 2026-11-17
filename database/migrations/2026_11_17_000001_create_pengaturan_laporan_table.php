<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_laporan', function (Blueprint $table) {
            $table->id();
            $table->decimal('foto_meter_lebar_mm', 5, 2)->default(38);
            $table->decimal('foto_meter_tinggi_mm', 5, 2)->default(27);
            $table->timestamps();
        });

        DB::table('pengaturan_laporan')->insert([
            'foto_meter_lebar_mm' => 38,
            'foto_meter_tinggi_mm' => 27,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_laporan');
    }
};
