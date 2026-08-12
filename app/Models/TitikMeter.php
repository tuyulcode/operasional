<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TitikMeter extends Model
{
    protected $table = 'titik_meter';
    protected $fillable = ['area_id', 'nama', 'meter_faktor', 'tarif_harga', 'status'];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function tagihanAir()
    {
        return $this->hasMany(TagihanAir::class, 'titik_meter_id');
    }
}
