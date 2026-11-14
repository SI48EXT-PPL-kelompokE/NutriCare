<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengaduan extends Model
{
    use HasFactory;

    protected $table = 'pengaduan';

    protected $fillable = [
        'user_id',
        'orang_tua_id',
        'kategori',
        'judul',
        'isi',
        'is_anonymous',
        'foto_bukti_path',
        'status',
        'catatan',
        'nomor_tiket',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($pengaduan) {
            if (empty($pengaduan->nomor_tiket)) {
                $pengaduan->nomor_tiket = static::generateNomorTiket();
            }
            if (empty($pengaduan->status)) {
                $pengaduan->status = 'baru';
            }
        });
    }

    public static function generateNomorTiket(): string
    {
        $last = static::latest('id')->first();
        $nextNumber = $last ? ($last->id + 1040) : 1001;
        $ticket = 'PGD-' . $nextNumber;
        while (static::where('nomor_tiket', $ticket)->exists()) {
            $nextNumber++;
            $ticket = 'PGD-' . $nextNumber;
        }
        return $ticket;
    }

    public function scopeSearch($query, ?string $keyword)
    {
        if (empty($keyword)) {
            return $query;
        }

        return $query->where(function ($q) use ($keyword) {
            $q->where('nomor_tiket', 'like', "%{$keyword}%")
              ->orWhere('judul', 'like', "%{$keyword}%")
              ->orWhere('isi', 'like', "%{$keyword}%")
              ->orWhereHas('orangTua', function ($sub) use ($keyword) {
                  $sub->where('name', 'like', "%{$keyword}%");
              });
        });
    }

    public function scopeKategori($query, ?string $kategori)
    {
        if (empty($kategori)) {
            return $query;
        }
        return $query->where('kategori', $kategori);
    }

    public function scopeStatus($query, ?string $status)
    {
        if (empty($status)) {
            return $query;
        }
        return $query->where('status', strtolower($status));
    }

    public function adminPenanggap()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orangTua()
    {
        return $this->belongsTo(OrangTua::class, 'orang_tua_id');
    }
}
