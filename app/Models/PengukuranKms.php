<?php

namespace App\Models;

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

    public function balita()
    {
        return $this->belongsTo(Balita::class, 'balita_id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
