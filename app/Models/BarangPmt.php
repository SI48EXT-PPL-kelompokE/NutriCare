<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangPmt extends Model
{
    use HasFactory;

    protected $table = 'barang_pmt';

    protected $fillable = [
        'nama',
        'kategori',
        'unit_type',
        'min_batas_stok',
        'energi_kcal',
        'protein',
        'lemak',
        'karbohidrat',
    ];

    protected function casts(): array
    {
        return [
            'energi_kcal' => 'decimal:2',
            'protein' => 'decimal:2',
            'lemak' => 'decimal:2',
            'karbohidrat' => 'decimal:2',
        ];
    }

    public function komposisiPaketPmt()
    {
        return $this->hasMany(KomposisiPaketPmt::class, 'barang_id');
    }

    public function stokGudang()
    {
        return $this->hasMany(StokGudang::class, 'barang_id');
    }
}
