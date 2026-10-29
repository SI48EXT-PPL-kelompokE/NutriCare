<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokGudang extends Model
{
    use HasFactory;

    protected $table = 'stok_gudang';

    protected $fillable = [
        'barang_id',
        'no_batch',
        'date_in',
        'exp_date',
        'stock_qty',
    ];

    protected function casts(): array
    {
        return [
            'date_in' => 'date',
            'exp_date' => 'date',
            'stock_qty' => 'decimal:2',
        ];
    }

    public function barang()
    {
        return $this->belongsTo(BarangPmt::class, 'barang_id');
    }

    public function detailDistribusiStok()
    {
        return $this->hasMany(DetailDistribusiStok::class, 'stok_id');
    }
}
