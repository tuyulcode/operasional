<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('area', function (Blueprint $table) {
            $table->string('format_rekap', 20)
                ->default('standar')
                ->after('kena_ppn')
                ->comment("Format rekap: 'standar' atau 'list'");
        });
    }

    public function down(): void
    {
        Schema::table('area', function (Blueprint $table) {
            $table->dropColumn('format_rekap');
        });
    }
};