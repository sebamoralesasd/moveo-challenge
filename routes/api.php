<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

/* Route::get('/user', function (Request $request) { */
/*     return $request->user(); */
/* })->middleware('auth:sanctum'); */

// Public
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/invitations/{hash}/redeem', [InvitationController::class, 'redeem']);
Route::get('/invitations/{hash}/tickets', [InvitationController::class, 'getTickets']);

// Admin
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('/invitations', [InvitationController::class, 'index']);
    Route::get('/events/{eventId}/tickets/used', [TicketController::class, 'getUsed']);
});

Route::middleware(['auth:api', 'role:checker'])->group(function () {
    Route::post('/tickets/{code}', [TicketController::class, 'validate']);
});
