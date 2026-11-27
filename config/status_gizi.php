<?php

return [
    // Rentang kewajaran WHO Anthro yang digunakan untuk menandai data perlu dicek.
    'rentang_kewajaran' => [
        'bb_u' => [-6.00, 5.00],
        'tb_u' => [-6.00, 6.00],
        'bb_tb' => [-5.00, 5.00],
    ],

    // Urutan ini adalah usulan teknis dan perlu dikonfirmasi tenaga kesehatan.
    'urutan_keparahan' => [
        'Gizi Buruk',
        'Sangat Pendek',
        'Berat Badan Sangat Kurang',
        'Gizi Kurang',
        'Stunting',
        'Berat Badan Kurang',
        'Obesitas',
        'Gizi Lebih',
        'Risiko Gizi Lebih',
        'Tinggi',
        'Normal',
    ],
];
