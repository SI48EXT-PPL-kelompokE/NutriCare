<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SesiKonseling extends Model
{
    use HasFactory;

    protected $table = 'sesi_konseling';

    protected $fillable = [
        'orang_tua_id',
        'user_id',
        'topik',
        'jadwal',
        'status',
        'hasil',
    ];

    protected function casts(): array
    {
        return [
            'jadwal' => 'datetime',
        ];
    }

    public function orangTua()
    {
        return $this->belongsTo(OrangTua::class, 'orang_tua_id');
    }

    public function nakes()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
