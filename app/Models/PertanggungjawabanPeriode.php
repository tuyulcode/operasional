<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PertanggungjawabanPeriode extends Model
{
    protected $table = 'pertanggungjawaban_periode';

    protected $fillable = ['bulan_label', 'tanggal_awal', 'tanggal_akhir'];

    protected $casts = [
        'tanggal_awal'  => 'date',
        'tanggal_akhir' => 'date',
    ];
}