<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KomposisiPaketPmt extends Model
{
    use HasFactory;

    protected $table = 'komposisi_paket_pmt';

    protected $fillable = [
        'paket_id',
        'barang_id',
        'jumlah',
        'unit_type',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
        ];
    }

    public function paket()
    {
        return $this->belongsTo(PaketPmt::class, 'paket_id');
    }

    public function barang()
    {
        return $this->belongsTo(BarangPmt::class, 'barang_id');
    }
}
