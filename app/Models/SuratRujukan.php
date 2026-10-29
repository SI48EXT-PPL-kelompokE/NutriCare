<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratRujukan extends Model
{
    use HasFactory;

    protected $table = 'surat_rujukan';

    protected $fillable = [
        'balita_id',
        'user_id',
        'nomor_surat',
        'faskes_tujuan',
        'status_follow_up',
        'alasan',
        'qr_verification_code',
    ];

    public function balita()
    {
        return $this->belongsTo(Balita::class, 'balita_id');
    }

    public function nakes()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
