<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistribusiPmt extends Model
{
    use HasFactory;

    protected $table = 'distribusi_pmt';

    protected $fillable = [
        'balita_id',
        'paket_id',
        'user_id',
        'jumlah',
        'status',
        'handover_date',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'handover_date' => 'date',
        ];
    }

    public function balita()
    {
        return $this->belongsTo(Balita::class, 'balita_id');
    }

    public function paket()
    {
        return $this->belongsTo(PaketPmt::class, 'paket_id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detailDistribusiStok()
    {
        return $this->hasMany(DetailDistribusiStok::class, 'distribusi_id');
    }
}
