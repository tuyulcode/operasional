<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kendaraan extends Model
{
    protected $table = 'kendaraan';
    protected $fillable = ['jenis_kendaraan_id', 'plat_nomor', 'nama_jenis', 'unit'];

    public function jenisKendaraan()
    {
        return $this->belongsTo(JenisKendaraan::class, 'jenis_kendaraan_id');
    }

    public function pemakaianBbm()
    {
        return $this->hasMany(PemakaianBbm::class, 'kendaraan_id');
    }
}