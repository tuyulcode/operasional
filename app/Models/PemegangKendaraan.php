<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemegangKendaraan extends Model
{
    protected $table = 'pemegang_kendaraan';
    protected $fillable = ['nama'];

    public function pemakaianEtoll()
    {
        return $this->hasMany(PemakaianEtoll::class, 'pemegang_kendaraan_id');
    }
}