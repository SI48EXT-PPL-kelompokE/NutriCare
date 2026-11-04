<?php

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

Route::apiResource('orang-tua', OrangTuaController::class);

