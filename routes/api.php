
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\RegistrationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ROUTE YANG MEMERLUKAN AUTENTIKASI
Route::middleware('auth:api')->group(function () {

    // LOGOUT
    Route::post('/logout', [AuthController::class, 'logout']);

    // PROFIL PENGGUNA
    Route::get('/profile', [AuthController::class, 'me']);
});

// ROUTE EVENT
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{id}', [EventController::class, 'show']);
Route::post('/events', [EventController::class, 'store']);
Route::put('/events/{id}', [EventController::class, 'update']);
Route::patch('/events/{id}', [EventController::class, 'update']);
Route::delete('/events/{id}', [EventController::class, 'destroy']);

// ROUTE TIKET
Route::apiResource('tickets', TicketController::class);

// ROUTE REGISTRASI
Route::get('/registrations', [RegistrationController::class, 'index']);
Route::get('/registrations/{id}', [RegistrationController::class, 'show']);
Route::post('/registrations', [RegistrationController::class, 'store']);
Route::put('/registrations/{id}', [RegistrationController::class, 'update']);
Route::patch('/registrations/{id}', [RegistrationController::class, 'update']);
Route::delete('/registrations/{id}', [RegistrationController::class, 'destroy']);
