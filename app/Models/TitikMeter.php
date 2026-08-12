<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TitikMeter extends Model
{
    protected $table = 'titik_meter';
    protected $fillable = ['pengambil_pemakai_id', 'nama', 'meter_faktor', 'tarif_harga', 'status'];

    public function pengambilPemakai()
    {
        return $this->belongsTo(PengambilPemakai::class, 'pengambil_pemakai_id');
    }

    public function tagihanAir()
    {
        return $this->hasMany(TagihanAir::class, 'titik_meter_id');
    }
}
