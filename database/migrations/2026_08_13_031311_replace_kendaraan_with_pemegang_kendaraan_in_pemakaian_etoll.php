<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pemakaian_etoll', function (Blueprint $table) {
            $table->dropForeign(['kendaraan_id']);
            $table->dropColumn('kendaraan_id');

            $table->unsignedInteger('pemegang_kendaraan_id')->after('id');
            $table->foreign('pemegang_kendaraan_id')->references('id')->on('pemegang_kendaraan');
        });
    }

    public function down(): void
    {
        Schema::table('pemakaian_etoll', function (Blueprint $table) {
            $table->dropForeign(['pemegang_kendaraan_id']);
            $table->dropColumn('pemegang_kendaraan_id');

            $table->unsignedInteger('kendaraan_id')->after('id');
            $table->foreign('kendaraan_id')->references('id')->on('kendaraan');
        });
    }
};
