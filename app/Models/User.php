<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'role_id',
        'name',
        'username',
        'email',
        'password',
        'nip',
        'wilayah_tugas',
        'active_status',
        'last_login',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'active_status' => 'boolean',
            'last_login' => 'datetime',
        ];
    }

    // Relasi: User belongsTo Role
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // Relasi: User hasOne OrangTua
    public function orangTua()
    {
        return $this->hasOne(OrangTua::class, 'user_id');
    }

    // Relasi: User hasMany PengukuranKms (sebagai petugas)
    public function pengukuranKms()
    {
        return $this->hasMany(PengukuranKms::class, 'user_id');
    }

    // Relasi: User hasMany Imunisasi (sebagai petugas)
    public function imunisasi()
    {
        return $this->hasMany(Imunisasi::class, 'user_id');
    }

    // Relasi: User hasMany PemberianVitamin (sebagai petugas)
    public function pemberianVitamin()
    {
        return $this->hasMany(PemberianVitamin::class, 'user_id');
    }

    // Relasi: User hasMany DistribusiPmt (sebagai petugas)
    public function distribusiPmt()
    {
        return $this->hasMany(DistribusiPmt::class, 'user_id');
    }

    // Relasi: User hasMany JadwalPosyandu (sebagai penanggung jawab)
    public function jadwalPosyandu()
    {
        return $this->hasMany(JadwalPosyandu::class, 'user_id');
    }

    // Relasi: User hasMany SuratRujukan (sebagai nakes)
    public function suratRujukan()
    {
        return $this->hasMany(SuratRujukan::class, 'user_id');
    }

    // Relasi: User hasMany Artikel (sebagai penulis)
    public function artikel()
    {
        return $this->hasMany(Artikel::class, 'user_id');
    }

    // Relasi: User hasMany SesiKonseling (sebagai nakes)
    public function sesiKonseling()
    {
        return $this->hasMany(SesiKonseling::class, 'user_id');
    }

    // Relasi: User hasMany Pengaduan (sebagai admin penanggap)
    public function pengaduan()
    {
        return $this->hasMany(Pengaduan::class, 'user_id');
    }
}
