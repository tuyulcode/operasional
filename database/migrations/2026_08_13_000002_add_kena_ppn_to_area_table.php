<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('area', function (Blueprint $table) {
            $table->boolean('kena_ppn')->default(false)->after('alamat')->comment('Area ini dikenakan PPN');
        });
    }

    public function down(): void
    {
        Schema::table('area', function (Blueprint $table) {
            $table->dropColumn('kena_ppn');
        });
    }
};