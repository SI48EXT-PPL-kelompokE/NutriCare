<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailDistribusiStok extends Model
{
    use HasFactory;

    protected $table = 'detail_distribusi_stok';

    protected $fillable = [
        'distribusi_id',
        'stok_id',
        'jumlah_diambil',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_diambil' => 'decimal:2',
        ];
    }

    public function distribusi()
    {
        return $this->belongsTo(DistribusiPmt::class, 'distribusi_id');
    }

    public function stok()
    {
        return $this->belongsTo(StokGudang::class, 'stok_id');
    }
}
