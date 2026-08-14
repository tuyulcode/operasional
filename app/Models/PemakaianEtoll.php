<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemakaianEtoll extends Model
{
    protected $table = 'pemakaian_etoll';
    protected $fillable = ['pemegang_kendaraan_id', 'tanggal', 'nominal', 'dicatat_oleh'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function pemegangKendaraan()
    {
        return $this->belongsTo(PemegangKendaraan::class, 'pemegang_kendaraan_id');
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}