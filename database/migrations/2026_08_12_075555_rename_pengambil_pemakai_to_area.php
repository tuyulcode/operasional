<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pengambil_pemakai')) {
            return;
        }

        Schema::table('titik_meter', function (Blueprint $table) {
            $table->dropForeign(['pengambil_pemakai_id']);
        });

        Schema::rename('pengambil_pemakai', 'area');

        Schema::table('titik_meter', function (Blueprint $table) {
            $table->renameColumn('pengambil_pemakai_id', 'area_id');
        });

        Schema::table('titik_meter', function (Blueprint $table) {
            $table->foreign('area_id')->references('id')->on('area');
        });
    }

    public function down(): void
    {
        Schema::table('titik_meter', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
        });

        Schema::table('titik_meter', function (Blueprint $table) {
            $table->renameColumn('area_id', 'pengambil_pemakai_id');
        });

        Schema::rename('area', 'pengambil_pemakai');

        Schema::table('titik_meter', function (Blueprint $table) {
            $table->foreign('pengambil_pemakai_id')->references('id')->on('pengambil_pemakai');
        });
    }
};
