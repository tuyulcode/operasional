<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'area';
    protected $fillable = ['nama', 'alamat', 'kena_ppn'];

    protected $casts = [
        'kena_ppn' => 'boolean',
    ];

    public function titikMeter()
    {
        return $this->hasMany(TitikMeter::class, 'area_id');
    }
}
