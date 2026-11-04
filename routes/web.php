<?php

use App\Http\Controllers\OrangTuaViewController;
use App\Http\Controllers\PaketPmtController;
use Illuminate\Support\Facades\Route;

// Landing page - default ke PMT Paket (sesuai PBI 8 yang sudah di-merge)
Route::get('/', function () {
    return redirect()->route('pmt.paket.index');
});

// Modul Logistik PMT - PBI 8: Master Paket PMT
Route::prefix('pmt')->name('pmt.')->group(function () {
    Route::post('paket/calculate-preview', [PaketPmtController::class, 'calculatePreview'])->name('paket.calculate-preview');
    Route::patch('paket/{paket}/toggle-status', [PaketPmtController::class, 'toggleStatus'])->name('paket.toggle-status');
    Route::resource('paket', PaketPmtController::class);
});

// Modul Kependudukan - Orang Tua (dari branch adhani)
Route::prefix('kependudukan')->name('kependudukan.')->group(function () {
    Route::get('orang-tua', [OrangTuaViewController::class, 'index'])->name('orang-tua.index');
});

// Legacy redirect
Route::redirect('/orang-tua', '/kependudukan/orang-tua');
