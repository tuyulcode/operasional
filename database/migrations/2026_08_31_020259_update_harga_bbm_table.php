<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Hapus kolom jenis
        Schema::table('harga_bbm', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });

        // 2. Rename kolom harga
        Schema::table('harga_bbm', function (Blueprint $table) {
            $table->renameColumn('harga_paiton', 'harga_pertamax');
            $table->renameColumn('harga_luar_paiton', 'harga_pertadex');
        });

        // 3. Isi dulu data lama yang masih NULL sebelum kolom dikunci jadi NOT NULL
        //    (misal: baris "solar" yang harga_pertadex-nya NULL)
        DB::table('harga_bbm')->whereNull('harga_pertadex')->update(['harga_pertadex' => 0]);

        // 4. Kunci harga_pertamax & harga_pertadex jadi NOT NULL
        Schema::table('harga_bbm', function (Blueprint $table) {
            $table->decimal('harga_pertamax', 10, 2)->nullable(false)->change();
            $table->decimal('harga_pertadex', 10, 2)->nullable(false)->change();
        });

        // 5. Tambah kolom baru, langsung NOT NULL dengan default value
        Schema::table('harga_bbm', function (Blueprint $table) {
            $table->date('tanggal_berlaku')->default(DB::raw('(CURRENT_DATE)'))->after('id');
            $table->decimal('harga_dexlite', 10, 2)->default(0)->after('harga_pertadex');
            $table->decimal('harga_pertamax_turbo', 10, 2)->default(0)->after('harga_dexlite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('harga_bbm', function (Blueprint $table) {
            $table->dropColumn(['tanggal_berlaku', 'harga_dexlite', 'harga_pertamax_turbo']);
        });

        Schema::table('harga_bbm', function (Blueprint $table) {
            $table->decimal('harga_pertadex', 10, 2)->nullable()->change();

            $table->renameColumn('harga_pertamax', 'harga_paiton');
            $table->renameColumn('harga_pertadex', 'harga_luar_paiton');
        });

        Schema::table('harga_bbm', function (Blueprint $table) {
            $table->string('jenis')->nullable();
        });
    }
};