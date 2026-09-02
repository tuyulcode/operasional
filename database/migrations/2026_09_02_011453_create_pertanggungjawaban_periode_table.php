<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pertanggungjawaban_periode', function (Blueprint $table) {
            $table->id();
            $table->string('bulan_label', 50);
            $table->date('tanggal_awal');
            $table->date('tanggal_akhir');
            $table->timestamps();

            $table->index('bulan_label');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pertanggungjawaban_periode');
    }
};