<?php

namespace App\Models;

use App\Enums\StatusGizi;
use App\Services\StatusGiziService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengukuranKms extends Model
{
    use HasFactory;

    protected $table = 'pengukuran_kms';

    protected $fillable = [
        'balita_id',
        'user_id',
        'date',
        'berat_badan',
        'tinggi_badan',
        'lingkar_lengan_atas',
        // Kolom turunan diisi otomatis oleh ZScoreService (KF-14) dan StatusGiziService (KF-15).
        // Tidak ada di validasi PengukuranKmsRequest, sehingga nilai dari request mentah tetap diabaikan.
        'z_score_bbu',
        'z_score_tbu',
        'z_score_bbtb',
        'status_gizi',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'berat_badan' => 'decimal:2',
            'tinggi_badan' => 'decimal:1',
            'lingkar_lengan_atas' => 'decimal:1',
            'z_score_bbu' => 'decimal:2',
            'z_score_tbu' => 'decimal:2',
            'z_score_bbtb' => 'decimal:2',
        ];
    }

    public function getStatusGiziAttribute($value): ?StatusGizi
    {
        return $value !== null ? StatusGizi::tryFrom($value) : null;
    }

    public function getHasilStatusAttribute(): array
    {
        return StatusGiziService::untukPengukuran($this);
    }

    public function scopeStatus($query, $status)
    {
        if ($status instanceof StatusGizi) {
            $status = $status->value;
        }

        return $query->where('status_gizi', $status);
    }

    public function scopeStunting($query)
    {
        return $query->where(function ($q) {
            $q->where('status_gizi', StatusGizi::STUNTING->value)
                ->orWhere('status_gizi', StatusGizi::SANGAT_PENDEK->value);
        });
    }

    public function scopeWasting($query)
    {
        return $query->where(function ($q) {
            $q->where('status_gizi', StatusGizi::GIZI_KURANG->value)
                ->orWhere('status_gizi', StatusGizi::GIZI_BURUK->value);
        });
    }

    public function scopePerluPerhatian($query)
    {
        return $query->whereNotNull('status_gizi')
            ->whereNotIn('status_gizi', [
                StatusGizi::NORMAL->value,
                StatusGizi::TINGGI->value,
            ]);
    }

    public function balita()
    {
        return $this->belongsTo(Balita::class, 'balita_id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Accessor: Hitung usia saat ukur dalam bulan bulat (bulatkan ke bawah)
     * Rumus: selisih bulan kalender penuh antara birth_date dan date
     */
    public function getUsiaBulanAttribute(): int
    {
        if (!$this->balita || !$this->balita->birth_date || !$this->date) {
            return 0;
        }

        // Carbon 3 diffInMonths bisa return float dan bertanda
        // Pastikan urutan benar (tanggal lahir -> tanggal ukur) dan floor
        $months = $this->balita->birth_date->floatDiffInMonths($this->date);
        return (int) floor(max(0, $months));
    }

    /**
     * Accessor: Label usia yang mudah dibaca
     * Contoh: "14 bulan (1 th 2 bln)" atau "5 bulan"
     */
    public function getUsiaLabelAttribute(): string
    {
        $months = $this->usia_bulan;

        if ($months < 12) {
            return "{$months} bulan";
        }

        $years = (int) floor($months / 12);
        $remainingMonths = $months % 12;

        if ($remainingMonths === 0) {
            return "{$months} bulan ({$years} th)";
        }

        return "{$months} bulan ({$years} th {$remainingMonths} bln)";
    }
}
