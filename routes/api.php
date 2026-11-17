<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;


Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

// ROUTE YANG MEMERLUKAN AUTENTIKASI
Route::middleware('auth:api')->group(function () {

    // LOGOUT
    Route::post('/logout', [AuthController::class, 'logout']);

    // PROFIL PENGGUNA
    Route::get('/profile', [AuthController::class, 'me']);
});
