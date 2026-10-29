<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandardLms extends Model
{
    use HasFactory;

    protected $table = 'standard_lms';

    protected $fillable = [
        'indikator',
        'jenis_kelamin',
        'acuan_nilai',
        'l',
        'm',
        's',
        'sd_min3',
        'sd_min2',
        'sd_min1',
        'sd_0',
        'sd_plus1',
        'sd_plus2',
        'sd_plus3',
    ];

    protected function casts(): array
    {
        return [
            'acuan_nilai' => 'decimal:1',
            'l' => 'decimal:5',
            'm' => 'decimal:5',
            's' => 'decimal:5',
            'sd_min3' => 'decimal:1',
            'sd_min2' => 'decimal:1',
            'sd_min1' => 'decimal:1',
            'sd_0' => 'decimal:1',
            'sd_plus1' => 'decimal:1',
            'sd_plus2' => 'decimal:1',
            'sd_plus3' => 'decimal:1',
        ];
    }
}
