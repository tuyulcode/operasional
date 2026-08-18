<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('tagihan_air');

        Schema::create('tagihan_air', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('titik_meter_id');
            $table->date('periode')->comment('Periode bulan tagihan');
            $table->decimal('meter_lalu', 15, 2)->default(0)->comment('Angka meter periode sebelumnya');
            $table->decimal('meter_ini', 15, 2)->default(0)->comment('Angka meter periode ini');
            $table->decimal('meter_faktor', 10, 2)->default(1)->comment('Pengali pemakaian');
            $table->decimal('tarif', 15, 2)->default(0)->comment('Tarif per m3');
            $table->decimal('pemakaian', 15, 2)->default(0)->comment('(meter_ini - meter_lalu) x meter_faktor');
            $table->decimal('jumlah', 15, 2)->default(0)->comment('Pemakaian terkoreksi x tarif');
            $table->string('foto', 255)->nullable();
            $table->timestamps();

            $table->unique(['titik_meter_id', 'periode']);
            $table->foreign('titik_meter_id')->references('id')->on('titik_meter');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan_air');

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
};