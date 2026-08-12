<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop default Laravel users table and recreate with our schema
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 100)->unique();
            $table->string('password_hash', 255);
            $table->string('role', 20)->comment('admin, petugas');
            $table->timestamps();
        });

        Schema::create('harga_bbm', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('harga_paiton', 15, 2)->comment('Harga BBM per liter di Paiton');
            $table->decimal('harga_luar_paiton', 15, 2)->comment('Harga BBM per liter di luar Paiton');
            $table->string('status', 20)->default('aktif')->comment('aktif, nonaktif');
            $table->timestamps();
        });

        Schema::create('jenis_kendaraan', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama_merek', 100)->unique()->comment('Contoh: Honda, Yamaha, Toyota, Suzuki');
            $table->timestamps();
        });

        Schema::create('kendaraan', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('jenis_kendaraan_id');
            $table->string('plat_nomor', 20)->unique();
            $table->string('nama_jenis', 50)->unique()->comment('Contoh: Roda 2, Roda 3, Roda 4');
            $table->timestamps();

            $table->foreign('jenis_kendaraan_id')->references('id')->on('jenis_kendaraan');
        });

        Schema::create('pemakaian_bbm', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('kendaraan_id');
            $table->unsignedInteger('harga_bbm_id');
            $table->date('tanggal');
            $table->decimal('liter_paiton', 10, 2)->default(0);
            $table->decimal('rp_paiton', 15, 2)->default(0)->comment('liter_paiton x harga_paiton');
            $table->decimal('liter_luar_paiton', 10, 2)->default(0);
            $table->decimal('rp_luar_paiton', 15, 2)->default(0)->comment('liter_luar_paiton x harga_luar_paiton');
            $table->decimal('service_oli', 15, 2)->default(0);
            $table->decimal('jasa', 15, 2)->default(0);
            $table->decimal('jumlah', 15, 2)->default(0)->comment('Total semua');
            $table->unsignedInteger('dicatat_oleh');
            $table->timestamps();

            $table->foreign('kendaraan_id')->references('id')->on('kendaraan');
            $table->foreign('harga_bbm_id')->references('id')->on('harga_bbm');
            $table->foreign('dicatat_oleh')->references('id')->on('users');
        });

        Schema::create('pemakaian_etoll', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('kendaraan_id');
            $table->date('tanggal');
            $table->decimal('nominal', 15, 2)->default(0);
            $table->unsignedInteger('dicatat_oleh');
            $table->timestamps();

            $table->foreign('kendaraan_id')->references('id')->on('kendaraan');
            $table->foreign('dicatat_oleh')->references('id')->on('users');
        });

        Schema::create('pengambil_pemakai', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama', 150)->comment('NAMA / PENGAMBIL / PEMAKAI');
            $table->string('alamat', 255)->nullable()->comment('ALAMAT');
            $table->timestamps();
        });

        Schema::create('ppn', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('persentase', 5, 2)->comment('Contoh: 11 untuk 11%');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->string('status', 20)->default('aktif')->comment('aktif, nonaktif');
            $table->timestamps();
        });

        Schema::create('titik_meter', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pengambil_pemakai_id');
            $table->string('nama', 100)->comment('Contoh: Barak 1, Barak 2, Wisma');
            $table->decimal('meter_faktor', 10, 2)->default(1);
            $table->decimal('tarif_harga', 15, 2)->default(0);
            $table->string('status', 20)->default('aktif')->comment('aktif, nonaktif');
            $table->timestamps();

            $table->foreign('pengambil_pemakai_id')->references('id')->on('pengambil_pemakai');
        });

        Schema::create('tagihan_air', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('titik_meter_id');
            $table->unsignedInteger('ppn_id');
            $table->date('bulan');
            $table->decimal('bulan_ini', 15, 2)->default(0)->comment('Bulan ini (a)');
            $table->decimal('bulan_lalu', 15, 2)->default(0)->comment('Bulan lalu (b)');
            $table->decimal('jumlah_pengambilan', 15, 2)->default(0)->comment('c = a - b');
            $table->decimal('meter_faktor', 10, 2)->default(1)->comment('d');
            $table->decimal('jumlah_pengambilan_faktor', 15, 2)->default(0)->comment('e = c x d');
            $table->decimal('tarif_harga', 15, 2)->default(0);
            $table->decimal('jumlah_sebelum_ppn', 15, 2)->default(0);
            $table->decimal('jumlah_ppn', 15, 2)->default(0);
            $table->decimal('jumlah_rp', 15, 2)->default(0)->comment('Total tagihan setelah PPN');
            $table->unsignedInteger('dicatat_oleh');
            $table->timestamps();

            $table->unique(['titik_meter_id', 'bulan']);
            $table->foreign('titik_meter_id')->references('id')->on('titik_meter');
            $table->foreign('ppn_id')->references('id')->on('ppn');
            $table->foreign('dicatat_oleh')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan_air');
        Schema::dropIfExists('titik_meter');
        Schema::dropIfExists('ppn');
        Schema::dropIfExists('pengambil_pemakai');
        Schema::dropIfExists('pemakaian_etoll');
        Schema::dropIfExists('pemakaian_bbm');
        Schema::dropIfExists('kendaraan');
        Schema::dropIfExists('jenis_kendaraan');
        Schema::dropIfExists('harga_bbm');
        Schema::dropIfExists('users');
    }
};
