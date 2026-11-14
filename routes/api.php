<?php

use App\Http\Controllers\Api\BalitaController;
use App\Http\Controllers\Api\OrangTuaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Di sini Anda dapat mendaftarkan rute API untuk aplikasi.
| Rute-rute ini dimuat oleh RouteServiceProvider atau Application configuration
| dalam grup yang diberi middleware "api".
|
*/

Route::middleware(['web', 'auth:web'])->group(function () {
    Route::apiResource('orang-tua', OrangTuaController::class)
        ->middlewareFor(['index', 'show'], "can:access-module,'orang_tua','view'")
        ->middlewareFor('store', "can:access-module,'orang_tua','create'")
        ->middlewareFor('update', "can:access-module,'orang_tua','edit'")
        ->middlewareFor('destroy', "can:access-module,'orang_tua','delete'");

    Route::apiResource('balita', BalitaController::class)
        ->parameters(['balita' => 'balita'])
        ->middlewareFor(['index', 'show'], "can:access-module,'balita','view'")
        ->middlewareFor('store', "can:access-module,'balita','create'")
        ->middlewareFor('update', "can:access-module,'balita','edit'")
        ->middlewareFor('destroy', "can:access-module,'balita','delete'");
});
