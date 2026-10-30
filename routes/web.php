<?php

use App\Http\Controllers\OrangTuaViewController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('kependudukan.orang-tua.index');
});

Route::get('/kependudukan/orang-tua', [OrangTuaViewController::class, 'index'])->name('kependudukan.orang-tua.index');
Route::redirect('/orang-tua', '/kependudukan/orang-tua');

