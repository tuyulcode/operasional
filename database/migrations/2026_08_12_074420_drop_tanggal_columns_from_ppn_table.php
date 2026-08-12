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
        if (Schema::hasColumn('ppn', 'tanggal_mulai') || Schema::hasColumn('ppn', 'tanggal_selesai')) {
            Schema::table('ppn', function (Blueprint $table) {
                $table->dropColumn(['tanggal_mulai', 'tanggal_selesai']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('ppn', function (Blueprint $table) {
            $table->date('tanggal_mulai')->after('persentase');
            $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
        });
    }
};
