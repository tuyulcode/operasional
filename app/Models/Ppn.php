<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ppn extends Model
{
    protected $table = 'ppn';

    // [BARU] persentase_realisasi = Persentase Perhitungan (dipakai menghitung nominal PPN)
    // persentase              = Persentase Laporan (yang ditampilkan di rekapan)
    protected $fillable = ['persentase', 'persentase_realisasi', 'status'];
}