<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penandatangan', function (Blueprint $table) {
            $table->increments('id');
            $table->string('jabatan', 100);
            $table->string('nama', 150)->nullable();
            $table->string('tempat', 100)->nullable();
            $table->timestamps();
        });

        DB::table('penandatangan')->insert([
            [
                'jabatan' => 'Manajer Bisnis Support',
                'nama' => null,
                'tempat' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jabatan' => 'Asman SDM Umum & CSR',
                'nama' => null,
                'tempat' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('penandatangan');
    }
};