<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SportDevsController;


Route::get('/volleyball/calendar', [SportDevsController::class, 'calendarFeed']);
Route::get('/volleyball/upcoming', [SportDevsController::class, 'apiUpcomingMatches']);

// routes/api.php
Route::get('/matches/{match}/seats', [SeatController::class, 'index']);
Route::post('/seats/{seat}/reserve', [SeatController::class, 'reserve'])->middleware('auth');

// API routes for SportDevs
