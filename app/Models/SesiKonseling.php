<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SesiKonseling extends Model
{
    use HasFactory;

    protected $table = 'sesi_konseling';

    public const TOPIK_LIST = [
        'Konseling Gizi Balita',
        'MPASI dan Pola Makan Sehat',
        'Gizi Ibu Hamil',
        'Stimulasi Tumbuh Kembang',
        'Pencegahan Stunting',
    ];

    protected $fillable = [
        'orang_tua_id',
        'user_id',
        'topik',
        'jadwal',
        'status',
        'hasil',
    ];

    protected function casts(): array
    {
        return [
            'jadwal' => 'datetime',
        ];
    }

    public function scopeStatus($query, ?string $status)
    {
        if (!empty($status) && $status !== 'all') {
            return $query->where('status', $status);
        }

        return $query;
    }

    public function scopeTopik($query, ?string $topik)
    {
        if (!empty($topik) && $topik !== 'all') {
            return $query->where('topik', $topik);
        }

        return $query;
    }

    public function scopeSearch($query, ?string $keyword)
    {
        if (!empty($keyword)) {
            return $query->where(function ($q) use ($keyword) {
                $q->where('topik', 'like', "%{$keyword}%")
                  ->orWhereHas('orangTua', function ($sub) use ($keyword) {
                      $sub->where('name', 'like', "%{$keyword}%")
                          ->orWhere('nik', 'like', "%{$keyword}%");
                  })
                  ->orWhereHas('nakes', function ($sub) use ($keyword) {
                      $sub->where('name', 'like', "%{$keyword}%");
                  });
            });
        }

        return $query;
    }

    public function orangTua()
    {
        return $this->belongsTo(OrangTua::class, 'orang_tua_id');
    }

    public function nakes()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
