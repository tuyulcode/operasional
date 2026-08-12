<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ppn extends Model
{
    protected $table = 'ppn';
    protected $fillable = ['persentase', 'tanggal_mulai', 'tanggal_selesai', 'status'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function tagihanAir()
    {
        return $this->hasMany(TagihanAir::class, 'ppn_id');
    }
}
