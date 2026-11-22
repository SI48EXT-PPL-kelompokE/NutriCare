<?php

use App\Http\Controllers\JadwalPosyanduController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\ReservasiController;
use App\Http\Controllers\SuratRujukanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Paket 4: Operasional & Rujukan Routes (Syifa)
|--------------------------------------------------------------------------
*/

// Endpoint publik verifikasi QR surat rujukan
Route::get('verifikasi-rujukan/{code}', [SuratRujukanController::class, 'verify'])->name('rujukan.verify');

Route::middleware('auth:web')->prefix('operasional')->group(function () {
    // PBI 11: Jadwal Posyandu
    Route::get('jadwal/feed', [JadwalPosyanduController::class, 'index'])->name('jadwal.feed');
    Route::patch('jadwal/{jadwal}/cancel', [JadwalPosyanduController::class, 'cancel'])->name('jadwal.cancel');
    Route::resource('jadwal', JadwalPosyanduController::class);

    // PBI 12: Presensi Posyandu via QR
    Route::get('presensi', [PresensiController::class, 'index'])->name('presensi.index');
    Route::post('presensi/check-in', [PresensiController::class, 'checkIn'])->name('presensi.check-in');
    Route::patch('presensi/{presensi}', [PresensiController::class, 'update'])->name('presensi.update');
    Route::delete('presensi/{presensi}', [PresensiController::class, 'destroy'])->name('presensi.destroy');

    // PBI 13: Reservasi & Reschedule Posyandu
    Route::patch('reservasi/{reservasi}/reschedule', [ReservasiController::class, 'reschedule'])->name('reservasi.reschedule');
    Route::patch('reservasi/{reservasi}/cancel', [ReservasiController::class, 'cancel'])->name('reservasi.cancel');
    Route::resource('reservasi', ReservasiController::class);

    // PBI 14: Surat Rujukan & Rekomendasi KMS
    Route::get('rujukan/{rujukan}/pdf', [SuratRujukanController::class, 'pdf'])->name('rujukan.pdf');
    Route::resource('rujukan', SuratRujukanController::class);
});
