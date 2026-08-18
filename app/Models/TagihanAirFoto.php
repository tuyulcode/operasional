<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TagihanAirFoto extends Model
{
    protected $table = 'tagihan_air_foto';

    protected $fillable = [
        'tagihan_air_id',
        'path_foto',
    ];

    public function tagihanAir()
    {
        return $this->belongsTo(TagihanAir::class, 'tagihan_air_id');
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->path_foto) {
            return null;
        }

        return str_starts_with($this->path_foto, 'uploads/')
            ? asset($this->path_foto)
            : Storage::disk('public')->url($this->path_foto);
    }

    public function getFilePathAttribute(): ?string
    {
        if (! $this->path_foto) {
            return null;
        }

        return str_starts_with($this->path_foto, 'uploads/')
            ? public_path($this->path_foto)
            : Storage::disk('public')->path($this->path_foto);
    }
}
