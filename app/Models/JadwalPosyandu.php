<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPosyandu extends Model
{
    use HasFactory;

    protected $table = 'jadwal_posyandu';

    protected $fillable = [
        'user_id',
        'judul',
        'date',
        'hour',
        'location',
        'kuota_jadwal',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function penanggungJawab()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reservasi()
    {
        return $this->hasMany(Reservasi::class, 'jadwal_id');
    }

    public function presensi()
    {
        return $this->hasMany(Presensi::class, 'jadwal_id');
    }
}
