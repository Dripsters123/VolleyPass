<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Api\SportDevsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketsController; 
use App\Http\Controllers\SeatController; 
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Pages
|--------------------------------------------------------------------------
*/


Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::get('/about', function () {
    return view('pages.about');
})->name('about');

Route::get('/contacts', function () {
    return view('pages.contacts');
})->name('contacts');

/*
|--------------------------------------------------------------------------
| Payment Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::post('/checkout', [PaymentController::class, 'checkout'])->name('payment.checkout');
    Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
});

Route::get('/payment-cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');

/*
|--------------------------------------------------------------------------
| Volleyball Routes
|--------------------------------------------------------------------------
*/
Route::get('/volleyball', [SportDevsController::class, 'upcomingMatches'])->name('volleyball.index');
Route::get('/volleyball/match/{id}', [SportDevsController::class, 'matchDetails'])->name('volleyball.show');
Route::get('/calendar', [SportDevsController::class, 'calendar'])->name('calendar.index');
Route::get('/calendar/past', [SportDevsController::class, 'pastCalendar'])->name('calendar.past');
Route::get('/calendar/match/{id}', [SportDevsController::class, 'matchDetails'])->name('calendar.match.show');
/*
|--------------------------------------------------------------------------
| Dashboard (Requires Auth)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
});


/*
|--------------------------------------------------------------------------
| Profile Routes (Requires Auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Tickets Routes (Requires Auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/tickets', [TicketsController::class, 'index'])->name('tickets.index');
});

Route::get('/calendar', [SportDevsController::class, 'calendar'])->name('calendar.index');


Route::get('/volleyball/matches/{id}', [SportDevsController::class, 'matchDetails'])
    ->name('volleyball.show');

Route::get('/volleyball/matches/past/{id}', [SportDevsController::class, 'pastMatchDetails'])
    ->name('volleyball.past.show');

    // routes/web.php
Route::post('/stripe/webhook', [PaymentController::class, 'webhook'])->name('stripe.webhook');

Route::get('/seats/{matchId}', [SeatController::class, 'index']);
Route::post('/seats/{seatId}/reserve', [SeatController::class, 'reserve']);
Route::get('/matches/{matchId}', [SeatController::class, 'show'])->name('matches.show');


require __DIR__.'/auth.php';
