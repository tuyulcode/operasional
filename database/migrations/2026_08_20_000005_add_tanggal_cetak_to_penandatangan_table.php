<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penandatangan', function (Blueprint $table) {
            $table->date('tanggal_cetak')->nullable()->after('tempat');
        });
    }

    public function down(): void
    {
        Schema::table('penandatangan', function (Blueprint $table) {
            $table->dropColumn('tanggal_cetak');
        });
    }
};
