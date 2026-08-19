<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('titik_meter', function (Blueprint $table) {
            $table->string('lokasi_flow_meter', 255)
                ->nullable()
                ->after('nama')
                ->comment('Lokasi fisik flow meter, misal AREA PLTU');
        });
    }

    public function down(): void
    {
        Schema::table('titik_meter', function (Blueprint $table) {
            $table->dropColumn('lokasi_flow_meter');
        });
    }
};