<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\RegistrationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {

    // AUTHENTICATION
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'me']);

    // AKSES USER DAN ADMIN: CREATE, READ, UPDATE
    Route::middleware('role:user,admin')->group(function () {

        // EVENTS
        Route::get('/events', [EventController::class, 'index']);
        Route::get('/events/{id}', [EventController::class, 'show']);
        Route::post('/events', [EventController::class, 'store']);
        Route::put('/events/{id}', [EventController::class, 'update']);
        Route::patch('/events/{id}', [EventController::class, 'update']);

        // TICKETS
        Route::apiResource('tickets', TicketController::class)
            ->except(['destroy']);

        // REGISTRATIONS
        Route::get('/registrations', [RegistrationController::class, 'index']);
        Route::get('/registrations/{id}', [RegistrationController::class, 'show']);
        Route::post('/registrations', [RegistrationController::class, 'store']);
        Route::put('/registrations/{id}', [RegistrationController::class, 'update']);
        Route::patch('/registrations/{id}', [RegistrationController::class, 'update']);

    });

    // AKSES ADMIN SAJA: DELETE
    Route::middleware('role:admin')->group(function () {

        // EVENTS
        Route::delete('/events/{id}', [EventController::class, 'destroy']);

        // TICKETS
        Route::delete('/tickets/{id}', [TicketController::class, 'destroy']);

        // REGISTRATIONS
        Route::delete('/registrations/{id}', [RegistrationController::class, 'destroy']);

    });

});
