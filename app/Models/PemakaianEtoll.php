<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemakaianEtoll extends Model
{
    protected $table = 'pemakaian_etoll';
    protected $fillable = ['kendaraan_id', 'tanggal', 'nominal', 'dicatat_oleh'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function kendaraan()
    {
        return $this->belongsTo(Kendaraan::class, 'kendaraan_id');
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
