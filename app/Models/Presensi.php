<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasFactory;

    protected $table = 'presensi';

    protected $fillable = [
        'jadwal_id',
        'balita_id',
        'presence_status',
    ];

    public function jadwal()
    {
        return $this->belongsTo(JadwalPosyandu::class, 'jadwal_id');
    }

    public function balita()
    {
        return $this->belongsTo(Balita::class, 'balita_id');
    }
}
