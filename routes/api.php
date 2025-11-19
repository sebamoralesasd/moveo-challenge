<?php

use App\Http\Controllers\InvitationController;
use App\Http\Controllers\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/invitations/{hash}', [InvitationController::class, 'redeem']);
Route::post('/tickets/{code}', [TicketController::class, 'validate']);
Route::get('/events/{eventId}/tickets/used', [TicketController::class, 'getUsed']);
