<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penandatangan extends Model
{
    public const MANAJER = 'Manajer Bisnis Support';

    public const ASMAN = 'Asman SDM Umum & CSR';

    protected $table = 'penandatangan';

    protected $fillable = ['jabatan', 'nama', 'tempat', 'tanggal_cetak'];
}
