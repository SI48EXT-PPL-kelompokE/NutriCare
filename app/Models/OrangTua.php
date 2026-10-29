<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrangTua extends Model
{
    use HasFactory;

    protected $table = 'orang_tua';

    protected $fillable = [
        'user_id',
        'nik',
        'name',
        'alamat',
        'kontak',
        'pregnancy_status',
    ];

    // Relasi: OrangTua belongsTo User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi: OrangTua hasMany Balita
    public function balita()
    {
        return $this->hasMany(Balita::class, 'orang_tua_id');
    }

    // Relasi: OrangTua hasMany Reservasi
    public function reservasi()
    {
        return $this->hasMany(Reservasi::class, 'orang_tua_id');
    }

    // Relasi: OrangTua hasMany SesiKonseling
    public function sesiKonseling()
    {
        return $this->hasMany(SesiKonseling::class, 'orang_tua_id');
    }

    // Relasi: OrangTua hasMany Pengaduan
    public function pengaduan()
    {
        return $this->hasMany(Pengaduan::class, 'orang_tua_id');
    }
}
