<?php

use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\OrangTuaViewController;
use App\Http\Controllers\PaketPmtController;
use App\Http\Controllers\SesiKonselingController;
use Illuminate\Support\Facades\Route;

// Landing page - default ke PMT Paket (sesuai PBI 8 yang sudah di-merge)
Route::get('/', function () {
    return redirect()->route('pmt.paket.index');
});

// Modul Edukasi & Layanan Warga - PBI 15: Artikel & Panduan Edukasi
Route::prefix('edukasi')->name('edukasi.')->group(function () {
    Route::get('artikel/baca/{slug}', [ArtikelController::class, 'baca'])->name('artikel.baca');
    Route::resource('artikel', ArtikelController::class);
});

// Modul Layanan Warga - PBI 16: Konseling Gizi Balita
Route::get('konseling/cek-ketersediaan', [SesiKonselingController::class, 'cekKetersediaan'])->name('konseling.cek-ketersediaan');
Route::patch('konseling/{konseling}/jadwalkan', [SesiKonselingController::class, 'jadwalkan'])->name('konseling.jadwalkan');
Route::patch('konseling/{konseling}/selesai', [SesiKonselingController::class, 'selesai'])->name('konseling.selesai');
Route::resource('konseling', SesiKonselingController::class);

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
