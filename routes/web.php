<?php

use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\BalitaViewController;
use App\Http\Controllers\ImunisasiController;
use App\Http\Controllers\OrangTuaViewController;
use App\Http\Controllers\PaketPmtController;
use App\Http\Controllers\PengukuranKmsController;
use App\Http\Controllers\SesiKonselingController;
use App\Http\Controllers\Settings\AccountController;
use App\Http\Controllers\Settings\PermissionMatrixController;
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
    Route::get('hak-akses', [PermissionMatrixController::class, 'index'])
        ->middleware("can:access-module,'hak_akses','view'")->name('permissions.index');
    Route::put('hak-akses', [PermissionMatrixController::class, 'update'])
        ->middleware("can:access-module,'hak_akses','edit'")->name('permissions.update');
});

// Modul Edukasi & Layanan Warga - PBI 15: Artikel & Panduan Edukasi
Route::prefix('edukasi')->name('edukasi.')->group(function () {
    Route::get('artikel/baca/{slug}', [ArtikelController::class, 'baca'])->name('artikel.baca');
    Route::middleware('auth:web')->group(function () {
        Route::get('artikel/export', [ArtikelController::class, 'export'])
            ->middleware("can:access-module,'laporan','create'")->name('artikel.export');
        Route::resource('artikel', ArtikelController::class)
            ->middlewareFor(['index', 'show'], "can:access-module,'artikel','view'")
            ->middlewareFor(['create', 'store'], "can:access-module,'artikel','create'")
            ->middlewareFor(['edit', 'update'], "can:access-module,'artikel','edit'")
            ->middlewareFor('destroy', "can:access-module,'artikel','delete'");
    });
});

// Modul Layanan Warga - PBI 16: Konseling Gizi Balita
Route::middleware('auth:web')->group(function () {
    Route::middleware("can:access-module,'konseling','edit'")->group(function () {
        Route::get('konseling/cek-ketersediaan', [SesiKonselingController::class, 'cekKetersediaan'])->name('konseling.cek-ketersediaan');
        Route::patch('konseling/{konseling}/jadwalkan', [SesiKonselingController::class, 'jadwalkan'])->name('konseling.jadwalkan');
        Route::patch('konseling/{konseling}/selesai', [SesiKonselingController::class, 'selesai'])->name('konseling.selesai');
    });
    Route::resource('konseling', SesiKonselingController::class)
        ->middlewareFor(['index', 'show'], "can:access-module,'konseling','view'")
        ->middlewareFor(['create', 'store'], "can:access-module,'konseling','create'")
        ->middlewareFor(['edit', 'update'], "can:access-module,'konseling','edit'")
        ->middlewareFor('destroy', "can:access-module,'konseling','delete'");
});

// Modul Logistik PMT - PBI 8: Master Paket PMT
Route::prefix('pmt')->name('pmt.')->middleware('auth:web')->group(function () {
    Route::post('paket/calculate-preview', [PaketPmtController::class, 'calculatePreview'])
        ->middleware("can:access-module,'paket_pmt','view'")->name('paket.calculate-preview');
    Route::patch('paket/{paket}/toggle-status', [PaketPmtController::class, 'toggleStatus'])
        ->middleware("can:access-module,'paket_pmt','edit'")->name('paket.toggle-status');
    Route::resource('paket', PaketPmtController::class)
        ->middlewareFor(['index', 'show'], "can:access-module,'paket_pmt','view'")
        ->middlewareFor(['create', 'store'], "can:access-module,'paket_pmt','create'")
        ->middlewareFor(['edit', 'update'], "can:access-module,'paket_pmt','edit'")
        ->middlewareFor('destroy', "can:access-module,'paket_pmt','delete'");
});

Route::middleware('auth:web')->group(function () {
    Route::resource('kms', PengukuranKmsController::class)
        ->parameters(['kms' => 'kms'])
        ->middlewareFor(['index', 'show'], ["can:access-module,'kms','view'", 'can:view-clinical-records'])
        ->middlewareFor(['create', 'store'], "can:access-module,'kms','create'")
        ->middlewareFor(['edit', 'update'], "can:access-module,'kms','edit'")
        ->middlewareFor('destroy', "can:access-module,'kms','delete'");

    Route::resource('imunisasi', ImunisasiController::class)
        ->parameters(['imunisasi' => 'imunisasi'])
        ->middlewareFor(['index', 'show'], "can:access-module,'imunisasi','view'")
        ->middlewareFor(['create', 'store'], "can:access-module,'imunisasi','create'")
        ->middlewareFor(['edit', 'update'], "can:access-module,'imunisasi','edit'")
        ->middlewareFor('destroy', "can:access-module,'imunisasi','delete'");
});

// Modul Kependudukan - Data Balita & Orang Tua (branch adhani)
Route::prefix('kependudukan')->name('kependudukan.')->middleware('auth:web')->group(function () {
    Route::middleware("can:access-module,'balita','view'")->group(function () {
        Route::get('balita', [BalitaViewController::class, 'index'])->name('balita.index');
        Route::get('balita/{balita}', [BalitaViewController::class, 'show'])->name('balita.show');
    });
    Route::get('orang-tua', [OrangTuaViewController::class, 'index'])
        ->middleware("can:access-module,'orang_tua','view'")->name('orang-tua.index');
});

// Legacy redirect
Route::redirect('/orang-tua', '/kependudukan/orang-tua');
Route::redirect('/balita', '/kependudukan/balita');
