<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HargaBbm extends Model
{
    protected $table = 'harga_bbm';

    protected $fillable = [
        'tanggal_berlaku',
        'harga_pertamax',
        'harga_pertadex',
        'harga_dexlite',
        'harga_pertamax_turbo',
    ];

    protected $casts = [
        'tanggal_berlaku' => 'date',
        'harga_pertamax' => 'decimal:2',
        'harga_pertadex' => 'decimal:2',
        'harga_dexlite' => 'decimal:2',
        'harga_pertamax_turbo' => 'decimal:2',
    ];

    public function pemakaianBbm()
    {
        return $this->hasMany(PemakaianBbm::class, 'harga_bbm_id');
    }

    /**
     * Cek apakah data harga ini sudah dipakai di tabel pemakaian BBM,
     * sehingga tidak boleh dihapus.
     */
    public function isDipakai(): bool
    {
        return $this->pemakaianBbm()->exists();
    }
}