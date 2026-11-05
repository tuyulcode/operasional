<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pemakaian_bbm', 'bukti_bbm')) {
            Schema::table('pemakaian_bbm', function (Blueprint $table) {
                $table->string('bukti_bbm')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pemakaian_bbm', 'bukti_bbm')) {
            Schema::table('pemakaian_bbm', function (Blueprint $table) {
                $table->dropColumn('bukti_bbm');
            });
        }
    }
};
