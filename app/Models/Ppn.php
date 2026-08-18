<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ppn extends Model
{
    protected $table = 'ppn';
    protected $fillable = ['persentase', 'status'];
}
