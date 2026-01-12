<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model {
    protected $fillable = [
        'device_id', 
        'lokasi', 
        'kapasitas_terakhir', 
        'kadar_bau_terakhir', 
        'waktu_pengangkutan'
    ];
}