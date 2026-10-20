<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('harga_bbm', function (Blueprint $table) {
            $table->decimal('harga_solar', 10, 2)->default(0)->after('harga_pertadex');
        });

        Schema::table('pemakaian_bbm', function (Blueprint $table) {
            $table->enum('jenis_bbm', ['pertamax', 'pertadex', 'dexlite', 'pertamax_turbo', 'solar'])
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        DB::table('pemakaian_bbm')->where('jenis_bbm', 'solar')->update(['jenis_bbm' => null]);

        Schema::table('pemakaian_bbm', function (Blueprint $table) {
            $table->enum('jenis_bbm', ['pertamax', 'pertadex', 'dexlite', 'pertamax_turbo'])
                ->nullable()
                ->change();
        });

        Schema::table('harga_bbm', function (Blueprint $table) {
            $table->dropColumn('harga_solar');
        });
    }
};
