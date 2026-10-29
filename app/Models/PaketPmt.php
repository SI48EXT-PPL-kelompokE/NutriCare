<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketPmt extends Model
{
    use HasFactory;

    protected $table = 'paket_pmt';

    protected $fillable = [
        'nama_paket_pmt',
        'deskripsi',
        'min_age_target',
        'max_age_target',
        'unit_type',
        'status',
        'total_energi_kcal',
        'total_protein',
        'total_lemak',
        'total_karbohidrat',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'total_energi_kcal' => 'decimal:2',
            'total_protein' => 'decimal:2',
            'total_lemak' => 'decimal:2',
            'total_karbohidrat' => 'decimal:2',
        ];
    }

    public function komposisiPaketPmt()
    {
        return $this->hasMany(KomposisiPaketPmt::class, 'paket_id');
    }

    public function distribusiPmt()
    {
        return $this->hasMany(DistribusiPmt::class, 'paket_id');
    }
}
