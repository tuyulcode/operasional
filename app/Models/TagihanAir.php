<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagihanAir extends Model
{
    protected $table = 'tagihan_air';
    protected $fillable = [
        'titik_meter_id', 'ppn_id', 'bulan',
        'bulan_ini', 'bulan_lalu', 'jumlah_pengambilan',
        'meter_faktor', 'jumlah_pengambilan_faktor',
        'tarif_harga', 'jumlah_sebelum_ppn', 'jumlah_ppn',
        'jumlah_rp', 'dicatat_oleh',
    ];

    protected $casts = [
        'bulan' => 'date',
    ];

    public function titikMeter()
    {
        return $this->belongsTo(TitikMeter::class, 'titik_meter_id');
    }

    public function ppn()
    {
        return $this->belongsTo(Ppn::class, 'ppn_id');
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
