<?php

use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\BalitaViewController;
use App\Http\Controllers\ImunisasiController;
use App\Http\Controllers\OrangTuaViewController;
use App\Http\Controllers\PaketPmtController;
use App\Http\Controllers\SesiKonselingController;
use App\Http\Controllers\Settings\AccountController;
use App\Http\Controllers\Settings\RoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SessionController::class, 'landing'])->name('home');
Route::get('login', [SessionController::class, 'create'])->name('login');
Route::post('login', [SessionController::class, 'store'])->name('login.store');
Route::post('logout', [SessionController::class, 'destroy'])->middleware('auth:web')->name('logout');


Route::prefix('pengaturan')->name('settings.')->middleware(['auth:web', 'can:manage-settings'])->group(function () {
    Route::resource('akun', AccountController::class)->names('accounts')->parameters(['akun' => 'account'])
        ->middlewareFor(['index', 'show'], "can:access-module,'akun','view'")
        ->middlewareFor(['create', 'store'], "can:access-module,'akun','create'")
        ->middlewareFor(['edit', 'update'], "can:access-module,'akun','edit'")
        ->middlewareFor('destroy', "can:access-module,'akun','delete'");
    Route::resource('role', RoleController::class)->names('roles')->except('show')
        ->middlewareFor('index', "can:access-module,'role','view'")
        ->middlewareFor(['create', 'store'], "can:access-module,'role','create'")
        ->middlewareFor(['edit', 'update'], "can:access-module,'role','edit'")
        ->middlewareFor('destroy', "can:access-module,'role','delete'");
});

// Modul Edukasi & Layanan Warga - PBI 15: Artikel & Panduan Edukasi
Route::prefix('edukasi')->name('edukasi.')->group(function () {
    Route::get('artikel/export', [ArtikelController::class, 'export'])->name('artikel.export');
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

// Modul KMS Digital - KF-13: CRUD Pengukuran Balita (KMS)
// TODO: tambahkan middleware auth dan role (nakes, kader) setelah autentikasi (Adhani) tersedia
Route::resource('kms', \App\Http\Controllers\PengukuranKmsController::class)->parameters([
    'kms' => 'kms'
]);

// Modul KMS Digital - KF-17: CRUD Riwayat Imunisasi
// TODO: tambahkan middleware auth dan role (nakes, kader) setelah autentikasi (Adhani) tersedia
Route::resource('imunisasi', ImunisasiController::class)->parameters([
    'imunisasi' => 'imunisasi'
]);

// Modul Kependudukan - Data Balita & Orang Tua (branch adhani)
Route::prefix('kependudukan')->name('kependudukan.')->group(function () {
    Route::get('balita', [BalitaViewController::class, 'index'])->name('balita.index');
    Route::get('balita/{balita}', [BalitaViewController::class, 'show'])->name('balita.show');
    Route::get('orang-tua', [OrangTuaViewController::class, 'index'])->name('orang-tua.index');
});

// Legacy redirect
Route::redirect('/orang-tua', '/kependudukan/orang-tua');
Route::redirect('/balita', '/kependudukan/balita');
