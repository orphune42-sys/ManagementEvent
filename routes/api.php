<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TicketController;


Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

// ROUTE YANG MEMERLUKAN AUTENTIKASI
Route::middleware('auth:api')->group(function () {

    // LOGOUT
    Route::post('/logout', [AuthController::class, 'logout']);

    // PROFIL PENGGUNA
    Route::get('/profile', [AuthController::class, 'me']);
});

Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{id}', [EventController::class, 'show']);
Route::post('/events', [EventController::class, 'store']);
Route::put('/events/{id}', [EventController::class, 'update']);
Route::patch('/events/{id}', [EventController::class, 'update']);
Route::delete('/events/{id}', [EventController::class, 'destroy']);
Route::apiResource('tickets', TicketController::class);
