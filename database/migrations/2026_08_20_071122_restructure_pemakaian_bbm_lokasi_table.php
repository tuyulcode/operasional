<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemakaian_bbm', function (Blueprint $table) {
            $table->renameColumn('liter_paiton', 'liter');
            $table->renameColumn('rp_paiton', 'rp');
        });

        Schema::table('pemakaian_bbm', function (Blueprint $table) {
            $table->enum('lokasi_pembelian', ['paiton', 'luar_paiton'])
                ->default('paiton')
                ->after('kendaraan_id');
        });

        Schema::table('pemakaian_bbm', function (Blueprint $table) {
            $table->dropColumn(['liter_luar_paiton', 'rp_luar_paiton']);
        });
    }

    public function down(): void
    {
        Schema::table('pemakaian_bbm', function (Blueprint $table) {
            $table->decimal('liter_luar_paiton', 10, 2)->nullable();
            $table->decimal('rp_luar_paiton', 15, 2)->nullable();
            $table->dropColumn('lokasi_pembelian');
        });

        Schema::table('pemakaian_bbm', function (Blueprint $table) {
            $table->renameColumn('liter', 'liter_paiton');
            $table->renameColumn('rp', 'rp_paiton');
        });
    }
};