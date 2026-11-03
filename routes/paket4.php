<?php

use App\Http\Controllers\JadwalPosyanduController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Paket 4: Operasional & Rujukan Routes (Syifa)
|--------------------------------------------------------------------------
*/

Route::prefix('operasional')->group(function () {
    // PBI 11: Jadwal Posyandu
    Route::get('jadwal/feed', [JadwalPosyanduController::class, 'index'])->name('jadwal.feed');
    Route::patch('jadwal/{jadwal}/cancel', [JadwalPosyanduController::class, 'cancel'])->name('jadwal.cancel');
    Route::resource('jadwal', JadwalPosyanduController::class);
});

