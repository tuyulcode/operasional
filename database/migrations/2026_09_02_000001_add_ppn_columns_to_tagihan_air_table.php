<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tagihan_air', function (Blueprint $table) {
            $table->decimal('ppn_persentase', 5, 2)->default(0)->after('tarif')
                ->comment('Persentase PPN saat data dibuat');
            $table->decimal('ppn_nominal', 15, 2)->default(0)->after('ppn_persentase')
                ->comment('Nilai PPN dalam Rupiah');
        });
    }

    public function down(): void
    {
        Schema::table('tagihan_air', function (Blueprint $table) {
            $table->dropColumn(['ppn_persentase', 'ppn_nominal']);
        });
    }
};
