<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisKendaraan extends Model
{
    protected $table = 'jenis_kendaraan';
    protected $fillable = ['nama_merek'];

    public function kendaraan()
    {
        return $this->hasMany(Kendaraan::class, 'jenis_kendaraan_id');
    }
}
