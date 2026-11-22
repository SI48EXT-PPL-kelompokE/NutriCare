<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservasi extends Model
{
    use HasFactory;

    protected $table = 'reservasi';

    protected $fillable = [
        'jadwal_id',
        'orang_tua_id',
        'balita_id',
        'status',
        'batas_reschedule',
    ];

    protected function casts(): array
    {
        return [
            'batas_reschedule' => 'datetime',
        ];
    }

    public function jadwal()
    {
        return $this->belongsTo(JadwalPosyandu::class, 'jadwal_id');
    }

    public function orangTua()
    {
        return $this->belongsTo(OrangTua::class, 'orang_tua_id');
    }

    public function balita()
    {
        return $this->belongsTo(Balita::class, 'balita_id');
    }

    /**
     * Tentukan apakah reservasi dapat di-reschedule.
     * Syarat: status belum selesai/batal dan waktu saat ini belum melewati batas_reschedule.
     */
    public function canReschedule(): bool
    {
        if (in_array($this->status, ['dibatalkan', 'selesai'], true)) {
            return false;
        }

        return $this->batas_reschedule !== null && now()->lt($this->batas_reschedule);
    }

    /**
     * Tentukan apakah reservasi aktif (memakai slot kuota).
     */
    public function isActive(): bool
    {
        return $this->status !== 'dibatalkan';
    }
}
