<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HargaBbm extends Model
{
    protected $table = 'harga_bbm';
    protected $fillable = ['jenis', 'harga_paiton', 'harga_luar_paiton'];

    public function pemakaianBbm()
    {
        return $this->hasMany(PemakaianBbm::class, 'harga_bbm_id');
    }
}
