<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemakaian_bbm', function (Blueprint $table) {
            $table->enum('jenis_bbm', ['pertamax', 'pertadex', 'dexlite', 'pertamax_turbo'])
                ->nullable()
                ->after('lokasi_pembelian');
        });
    }

    public function down(): void
    {
        Schema::table('pemakaian_bbm', function (Blueprint $table) {
            $table->dropColumn('jenis_bbm');
        });
    }
};