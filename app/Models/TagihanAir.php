<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagihanAir extends Model
{
    protected $table = 'tagihan_air';

    protected $fillable = [
        'titik_meter_id', 'periode',
        'meter_lalu', 'meter_ini',
        'meter_faktor', 'tarif',
        'pemakaian', 'jumlah',
        'foto',
    ];

    protected $casts = [
        'periode' => 'date',
    ];

    public function titikMeter()
    {
        return $this->belongsTo(TitikMeter::class, 'titik_meter_id');
    }

    public function fotos()
    {
        return $this->hasMany(TagihanAirFoto::class, 'tagihan_air_id');
    }
}
