<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengambilPemakai extends Model
{
    protected $table = 'pengambil_pemakai';
    protected $fillable = ['nama', 'alamat'];

    public function titikMeter()
    {
        return $this->hasMany(TitikMeter::class, 'pengambil_pemakai_id');
    }
}
