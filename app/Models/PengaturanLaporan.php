<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanLaporan extends Model
{
    public const DEFAULT_FOTO_LEBAR_MM = 38.0;

    public const DEFAULT_FOTO_TINGGI_MM = 27.0;

    protected $table = 'pengaturan_laporan';

    protected $fillable = [
        'foto_meter_lebar_mm',
        'foto_meter_tinggi_mm',
    ];

    protected $casts = [
        'foto_meter_lebar_mm' => 'float',
        'foto_meter_tinggi_mm' => 'float',
    ];

    /**
     * Baris pengaturan tunggal. Kalau baris belum ada (mis. migration belum
     * dijalankan), pakai nilai default supaya export tetap berjalan normal.
     */
    public static function current(): self
    {
        return static::query()->first() ?? new static([
            'foto_meter_lebar_mm' => self::DEFAULT_FOTO_LEBAR_MM,
            'foto_meter_tinggi_mm' => self::DEFAULT_FOTO_TINGGI_MM,
        ]);
    }
}
