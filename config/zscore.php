<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Koreksi Nilai Ekstrem WHO
    |--------------------------------------------------------------------------
    |
    | WHO merekomendasikan koreksi khusus untuk z-score > 3 atau < -3
    | pada indikator berbasis berat badan (BB_U, BB_PB, BB_TB).
    |
    | Rumus koreksi:
    | - Jika Z > 3: Z = 3 + (nilai - SD(+3)) / (SD(+3) - SD(+2))
    | - Jika Z < -3: Z = -3 + (nilai - SD(-3)) / (SD(-2) - SD(-3))
    |
    | Untuk TB_U (tinggi/panjang menurut umur), koreksi TIDAK diterapkan.
    |
    | CATATAN PENTING:
    | Nilai ekstrem (di luar ±3 SD) menunjukkan kondisi yang perlu perhatian
    | khusus dan harus dikonfirmasi oleh tenaga kesehatan profesional.
    | Koreksi ini hanya metode statistik untuk menandai nilai outlier.
    |
    */

    'extreme_adjustment' => [
        'BB_U' => true,   // Berat badan menurut umur
        'TB_U' => false,  // Tinggi/panjang badan menurut umur
        'BB_PB' => true,  // Berat badan menurut panjang badan (< 24 bulan)
        'BB_TB' => true,  // Berat badan menurut tinggi badan (>= 24 bulan)
    ],

];
