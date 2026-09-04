<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Mapping teks lama -> teks baru buat kolom `jabatan` di tabel
     * `penandatangans`. Ditulis sebagai pasangan biar migration ini bisa
     * di-rollback (down()) balikin ke teks semula.
     */
    private array $map = [
        'Manajer Bisnis Support' => 'Manager Business Support',
        'Asman SDM Umum & CSR' => 'Asman SDM, Umum & CSR',
    ];

    public function up(): void
    {
        foreach ($this->map as $old => $new) {
            DB::table('penandatangan')
                ->where('jabatan', $old)
                ->update(['jabatan' => $new]);
        }
    }

    public function down(): void
    {
        foreach ($this->map as $old => $new) {
            DB::table('penandatangan')
                ->where('jabatan', $new)
                ->update(['jabatan' => $old]);
        }
    }
};