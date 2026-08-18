<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan_air_foto', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tagihan_air_id');
            $table->string('path_foto', 255);
            $table->timestamps();

            $table->foreign('tagihan_air_id')
                ->references('id')->on('tagihan_air')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan_air_foto');
    }
};
