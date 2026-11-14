<?php

return [
    'driver' => 'bcrypt',

    'bcrypt' => [
        'rounds' => max(10, (int) env('BCRYPT_ROUNDS', 12)),
        'verify' => true,
    ],
];
