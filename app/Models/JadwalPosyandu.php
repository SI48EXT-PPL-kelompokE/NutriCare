<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'jam_selesai',
        'location',
        'kuota_jadwal',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'kuota_jadwal' => 'integer',
        ];
    }

    /**
     * Alias untuk kolom hour sebagai jam_mulai.
     */
    protected function jamMulai(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->hour,
            set: fn ($value) => ['hour' => $value],
        );
    }

    /**
     * Timestamp lengkap awal jadwal.
     */
    public function getStartsAtAttribute(): ?Carbon
    {
        if (!$this->date || !$this->hour) {
            return null;
        }

        $dateStr = $this->date instanceof Carbon ? $this->date->format('Y-m-d') : (string) $this->date;
        return Carbon::parse($dateStr . ' ' . $this->hour, config('app.timezone', 'Asia/Jakarta'));
    }

    /**
     * Timestamp lengkap akhir jadwal.
     */
    public function getEndsAtAttribute(): ?Carbon
    {
        if (!$this->date || !$this->jam_selesai) {
            return null;
        }

        $dateStr = $this->date instanceof Carbon ? $this->date->format('Y-m-d') : (string) $this->date;
        return Carbon::parse($dateStr . ' ' . $this->jam_selesai, config('app.timezone', 'Asia/Jakarta'));
    }

    /**
     * Scope jadwal yang masih aktif (belum dibatalkan).
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope deteksi bentrok jadwal untuk nakes yang sama pada rentang waktu.
     * Interval setengah terbuka [mulai, selesai):
     * existing.hour < new.jam_selesai AND existing.jam_selesai > new.hour
     */
    public function scopeOverlap(
        Builder $query,
        int $userId,
        string $date,
        string $jamMulai,
        string $jamSelesai,
        ?int $excludeId = null
    ): Builder {
        $query->where('user_id', $userId)
            ->where('date', $date)
            ->where('status', 'aktif')
            ->where('hour', '<', $jamSelesai)
            ->where('jam_selesai', '>', $jamMulai);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query;
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
