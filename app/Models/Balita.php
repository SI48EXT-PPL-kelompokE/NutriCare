<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balita extends Model
{
    use HasFactory;

    protected $table = 'balita';

    protected $fillable = [
        'orang_tua_id',
        'name',
        'birth_date',
        'jenis_kelamin',
        'qr_code',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function orangTua()
    {
        return $this->belongsTo(OrangTua::class, 'orang_tua_id');
    }

    public function pengukuranKms()
    {
        return $this->hasMany(PengukuranKms::class, 'balita_id');
    }

    public function imunisasi()
    {
        return $this->hasMany(Imunisasi::class, 'balita_id');
    }

    public function pemberianVitamin()
    {
        return $this->hasMany(PemberianVitamin::class, 'balita_id');
    }

    public function distribusiPmt()
    {
        return $this->hasMany(DistribusiPmt::class, 'balita_id');
    }

    public function presensi()
    {
        return $this->hasMany(Presensi::class, 'balita_id');
    }

    public function suratRujukan()
    {
        return $this->hasMany(SuratRujukan::class, 'balita_id');
    }

    public function reservasi()
    {
        return $this->hasMany(Reservasi::class, 'balita_id');
    }
}
