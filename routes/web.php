<?php

use App\Http\Controllers\PaketPmtController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('pmt.paket.index');
});

// Modul Logistik PMT - PBI 8: Master Paket PMT
Route::prefix('pmt')->name('pmt.')->group(function () {
    Route::post('paket/calculate-preview', [PaketPmtController::class, 'calculatePreview'])->name('paket.calculate-preview');
    Route::patch('paket/{paket}/toggle-status', [PaketPmtController::class, 'toggleStatus'])->name('paket.toggle-status');
    Route::resource('paket', PaketPmtController::class);
});
