<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('harga_bbm', 'jenis')) {
            return;
        }

        Schema::table('harga_bbm', function (Blueprint $table) {
            $table->string('jenis', 20)->nullable()->after('id');
        });

        DB::table('harga_bbm')->update(['jenis' => 'bensin']);

        // Keep only one row per jenis (keep the earliest)
        $keptIds = DB::table('harga_bbm')
            ->selectRaw('MIN(id) as id')
            ->groupBy('jenis')
            ->pluck('id');

        DB::table('harga_bbm')
            ->whereNotIn('id', $keptIds)
            ->delete();

        Schema::table('harga_bbm', function (Blueprint $table) {
            $table->string('jenis', 20)->nullable(false)->change();
            $table->unique('jenis');
        });
    }

    public function down(): void
    {
        Schema::table('harga_bbm', function (Blueprint $table) {
            $table->dropUnique(['jenis']);
            $table->dropColumn('jenis');
        });
    }
};
