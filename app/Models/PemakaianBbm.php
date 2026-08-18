<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemakaianBbm extends Model
{
    protected $table = 'pemakaian_bbm';
    protected $fillable = [
        'kendaraan_id', 'harga_bbm_id', 'tanggal',
        'liter_paiton', 'rp_paiton', 'liter_luar_paiton', 'rp_luar_paiton',
        'service_oli', 'jasa', 'jumlah', 'dicatat_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function kendaraan()
    {
        return $this->belongsTo(Kendaraan::class, 'kendaraan_id');
    }

    public function hargaBbm()
    {
        return $this->belongsTo(HargaBbm::class, 'harga_bbm_id');
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}