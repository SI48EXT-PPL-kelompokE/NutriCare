<?php

namespace App\Enums;

/**
 * Enum untuk status gizi balita berdasarkan klasifikasi antropometri WHO
 * 
 * Sumber: Permenkes No. 2 Tahun 2020 tentang Standar Antropometri Anak
 * Pasal 4 dan Lampiran (usia 0-60 bulan)
 */
enum StatusGizi: string
{
    // Status kritis (warna merah)
    case GIZI_BURUK = 'Gizi Buruk';
    case SANGAT_PENDEK = 'Sangat Pendek';
    case BERAT_BADAN_SANGAT_KURANG = 'Berat Badan Sangat Kurang';
    case OBESITAS = 'Obesitas';
    
    // Status perlu perhatian (warna kuning/oranye)
    case GIZI_KURANG = 'Gizi Kurang';
    case STUNTING = 'Stunting';
    case BERAT_BADAN_KURANG = 'Berat Badan Kurang';
    
    // Status perlu pemantauan (warna biru/kuning lembut)
    case RISIKO_GIZI_LEBIH = 'Risiko Gizi Lebih';
    case GIZI_LEBIH = 'Gizi Lebih';
    case TINGGI = 'Tinggi';
    
    // Status normal (warna hijau)
    case NORMAL = 'Normal';

    /**
     * Label yang ditampilkan di UI
     */
    public function label(): string
    {
        return $this->value;
    }

    /**
     * Tone warna untuk komponen UI (pill/badge)
     */
    public function tone(): string
    {
        return match($this) {
            self::GIZI_BURUK, 
            self::SANGAT_PENDEK, 
            self::BERAT_BADAN_SANGAT_KURANG, 
            self::OBESITAS => 'danger',
            
            self::GIZI_KURANG, 
            self::STUNTING, 
            self::BERAT_BADAN_KURANG => 'warning',
            
            self::RISIKO_GIZI_LEBIH,
            self::GIZI_LEBIH,
            self::TINGGI => 'info',
            
            self::NORMAL => 'success',
        };
    }

    /**
     * Deskripsi lengkap status gizi
     */
    public function deskripsi(): string
    {
        return match($this) {
            self::GIZI_BURUK => 'BB/TB < -3 SD (wasting berat), memerlukan penanganan segera',
            self::SANGAT_PENDEK => 'TB/U < -3 SD (stunting berat), pertumbuhan linear sangat terhambat',
            self::BERAT_BADAN_SANGAT_KURANG => 'BB/U < -3 SD (underweight berat), berat badan sangat rendah untuk usianya',
            self::OBESITAS => 'BB/TB > +3 SD, kelebihan berat badan ekstrem',
            self::GIZI_KURANG => 'BB/TB -3 SD sampai < -2 SD (wasting), memerlukan pemantauan',
            self::STUNTING => 'TB/U -3 SD sampai < -2 SD, pertumbuhan linear terhambat',
            self::BERAT_BADAN_KURANG => 'BB/U -3 SD sampai < -2 SD (underweight), berat badan rendah untuk usianya',
            self::RISIKO_GIZI_LEBIH => 'BB/TB atau BB/U > +1 SD, perlu pemantauan asupan',
            self::GIZI_LEBIH => 'BB/TB +2 SD sampai +3 SD, kelebihan berat badan',
            self::TINGGI => 'TB/U > +3 SD, tinggi badan di atas normal',
            self::NORMAL => 'Semua indeks dalam batas normal WHO',
        };
    }

    /**
     * Prioritas keparahan untuk penentuan status utama
     * Nilai lebih kecil = prioritas lebih tinggi (lebih kritis)
     * 
     * CATATAN: Urutan ini adalah usulan teknis berdasarkan tingkat keparahan
     * dan perlu dikonfirmasi oleh tenaga kesehatan profesional
     */
    public function prioritas(): int
    {
        return match($this) {
            self::GIZI_BURUK => 1,
            self::SANGAT_PENDEK => 2,
            self::BERAT_BADAN_SANGAT_KURANG => 3,
            self::GIZI_KURANG => 4,
            self::STUNTING => 5,
            self::BERAT_BADAN_KURANG => 6,
            self::OBESITAS => 7,
            self::GIZI_LEBIH => 8,
            self::RISIKO_GIZI_LEBIH => 9,
            self::TINGGI => 10,
            self::NORMAL => 11,
        };
    }
}
