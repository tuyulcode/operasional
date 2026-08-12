<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('harga_bbm', 'status')) {
            Schema::table('harga_bbm', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }

    public function down(): void
    {
        Schema::table('harga_bbm', function (Blueprint $table) {
            $table->string('status', 20)->default('aktif')->after('harga_luar_paiton');
        });
    }
};
