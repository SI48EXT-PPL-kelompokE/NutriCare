<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Artikel extends Model
{
    use HasFactory;

    protected $table = 'artikel';

    protected $fillable = [
        'user_id',
        'judul',
        'kategori',
        'status',
        'url_slug',
        'konten',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($artikel) {
            if (empty($artikel->url_slug)) {
                $artikel->url_slug = static::generateUniqueSlug($artikel->judul);
            }
            if ($artikel->status === 'published' && empty($artikel->published_at)) {
                $artikel->published_at = now();
            }
        });

        static::updating(function ($artikel) {
            if ($artikel->isDirty('judul') && empty($artikel->url_slug)) {
                $artikel->url_slug = static::generateUniqueSlug($artikel->judul, $artikel->id);
            }
            if ($artikel->isDirty('status')) {
                if ($artikel->status === 'published' && empty($artikel->published_at)) {
                    $artikel->published_at = now();
                } elseif ($artikel->status === 'draft') {
                    $artikel->published_at = null;
                }
            }
        });
    }

    /**
     * Menghasilkan slug unik secara otomatis dari judul artikel.
     */
    public static function generateUniqueSlug(string $judul, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($judul);
        $slug = $baseSlug ?: 'artikel';
        $counter = 1;

        while (static::where('url_slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Scope untuk artikel yang sudah terbit.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope untuk artikel berstatus draft.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope filter berdasarkan kategori materi.
     */
    public function scopeKategori($query, ?string $kategori)
    {
        if (!empty($kategori) && $kategori !== 'all') {
            return $query->where('kategori', $kategori);
        }

        return $query;
    }

    /**
     * Scope pencarian berdasarkan kata kunci pada judul atau isi artikel.
     */
    public function scopeSearch($query, ?string $keyword)
    {
        if (!empty($keyword)) {
            return $query->where(function ($q) use ($keyword) {
                $q->where('judul', 'like', "%{$keyword}%")
                  ->orWhere('konten', 'like', "%{$keyword}%");
            });
        }

        return $query;
    }

    public function penulis()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
