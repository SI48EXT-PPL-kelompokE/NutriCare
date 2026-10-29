<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemberianVitamin extends Model
{
    use HasFactory;

    protected $table = 'pemberian_vitamin';

    protected $fillable = [
        'balita_id',
        'user_id',
        'jenis_vit',
        'dosis',
        'date',
        'periode',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
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
