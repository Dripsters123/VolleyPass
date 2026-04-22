<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    PaymentController,
    ProfileController,
    TicketsController,
    SeatController,
    DashboardController,
    HomeController,
    MatchController,
    CalendarController,
    TicketClaimController,
    MatchRequestController,
    ProductController,
    WalletController,
    ProductRequestController,
    ArenaController,
};

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', fn () => view('pages.about'))->name('about');
Route::get('/contacts', fn () => view('pages.contacts'))->name('contacts');

Route::middleware('auth')->group(function () {
    Route::post('/checkout', [PaymentController::class, 'checkout'])->name('payment.checkout');
    Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
});
Route::get('/payment-cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');


Route::get('/volleyball', [MatchController::class, 'index'])->name('volleyball.index');
Route::get('/volleyball/{id}', [MatchController::class, 'localShow'])->where('id', '[0-9]+')->name('volleyball.show');
Route::get('/volleyball/match/{id}', [MatchController::class, 'localShow'])->where('id', '[0-9]+');
Route::get('/volleyball/matches/{id}', [MatchController::class, 'localShow'])->where('id', '[0-9]+');

Route::get('/local-matches', [MatchController::class, 'index'])->name('local.matches.index');
Route::get('/local-matches/{id}', [MatchController::class, 'localShow'])->name('local.matches.show');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::post('/payment/validate-discount', [PaymentController::class, 'validateDiscount'])
        ->name('payment.validateDiscount');
});

Route::middleware('auth')->group(function () {
    Route::resource('arenas', ArenaController::class);
    Route::post('/arenas/{arena}/duplicate', [ArenaController::class, 'duplicate'])->name('arenas.duplicate');
    Route::post('/arenas/{arena}/favorite', [ArenaController::class, 'toggleFavorite'])->name('arenas.favorite');
    Route::post('/arenas/{arena}/toggle-public', [ArenaController::class, 'togglePublic'])->name('arenas.togglePublic');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/wallet', [WalletController::class, 'show'])->name('wallet.show');
    Route::post('/wallet/buy-discount', [WalletController::class, 'buyDiscountCard'])->name('wallet.buy.post');
    Route::get('/wallet/discount-cards', [WalletController::class, 'listDiscountCards'])->name('wallet.cards.list');
});
    Route::get('/tickets', [TicketsController::class, 'index'])->name('tickets.index');
    Route::post('/tickets/{ticket}/claim', [TicketClaimController::class, 'claim'])->name('tickets.claim');
    Route::post('/tickets/{ticket}/admin-claim', [TicketClaimController::class, 'adminClaim'])
        ->name('tickets.admin_claim')->middleware('role:admin');

    Route::post('/seats/{seatId}/reserve', [SeatController::class, 'reserve']);


Route::get('/seats/{matchId}', [SeatController::class, 'index']);
Route::get('/matches/{matchId}', [SeatController::class, 'show'])->name('matches.show');


Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');


Route::middleware('auth')->group(function () {
    Route::get('/match-request/create', [MatchRequestController::class, 'create'])->name('match_requests.create');
    Route::post('/match-request/store', [MatchRequestController::class, 'store'])->name('match_requests.store');

    Route::get('/my-requests', [MatchRequestController::class, 'myRequests'])->name('match_requests.my');
    Route::get('/match-requests/{matchRequest}', [MatchRequestController::class, 'show'])->name('match_requests.show');
    Route::get('/match-requests/{matchRequest}/edit', [MatchRequestController::class, 'edit'])->name('match_requests.edit');
    Route::put('/match-requests/{matchRequest}', [MatchRequestController::class, 'update'])->name('match_requests.update');
    Route::delete('/match-requests/{matchRequest}/cancel', [MatchRequestController::class, 'cancel'])->name('match_requests.cancel');
    Route::get('/my-requests/{id}', [MatchRequestController::class, 'view'])->name('match_requests.view');
});


Route::middleware('auth')->group(function () {
    Route::post('/matches/{id}/score-request', [MatchController::class, 'submitScoreRequest'])->name('matches.score.request');
    Route::post('/matches/verification/{id}/confirm', [MatchController::class, 'confirmScore'])->name('matches.score.confirm');
    Route::post('/matches/{id}/media', [MatchController::class, 'uploadMedia'])->name('matches.media.upload');
});


Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::post('/matches/{id}/finalize', [MatchController::class, 'finalizeScore'])->name('matches.score.finalize');
});


Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [MatchRequestController::class, 'inbox'])->name('matches.index');

        Route::get('/matches/create', [MatchController::class, 'create'])->name('matches.create');
        Route::post('/matches', [MatchController::class, 'store'])->name('matches.store');
        Route::get('/matches/{id}/edit', [MatchController::class, 'adminEdit'])->name('matches.edit');
        Route::put('/matches/{id}', [MatchController::class, 'adminUpdate'])->name('matches.update');
        Route::delete('/matches/{id}', [MatchController::class, 'adminDestroy'])->name('matches.destroy');


        Route::get('/inbox', [MatchRequestController::class, 'inbox'])->name('match_requests.inbox');
        Route::get('/inbox/{id}', [MatchRequestController::class, 'show'])->name('match_requests.show');
        Route::post('/inbox/{id}/accept', [MatchRequestController::class, 'accept'])->name('match_requests.accept');
        Route::post('/inbox/{id}/reject', [MatchRequestController::class, 'reject'])->name('match_requests.reject');

      
        Route::get('/product-requests', [ProductRequestController::class, 'adminIndex'])->name('product_requests.index');
        Route::get('/product-requests/{productRequest}', [ProductRequestController::class, 'show'])->name('product_requests.show');
        Route::get('/product-requests/{productRequest}/edit', [ProductRequestController::class, 'editForAdmin'])->name('product_requests.edit');
        Route::post('/product-requests/{productRequest}/approve', [ProductRequestController::class, 'approve'])->name('product_requests.approve');
        Route::post('/product-requests/{productRequest}/reject', [ProductRequestController::class, 'reject'])->name('product_requests.reject');
    });


Route::get('/products', [ProductController::class, 'index'])->name('products.index');


Route::middleware('auth')->group(function () {
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
Route::delete('/product-requests/{productRequest}/cancel', [ProductRequestController::class, 'cancel'])
        ->name('product_requests.cancel');
    Route::post('/products/{product}/buy', [ProductController::class, 'buy'])->name('products.buy');
    Route::get('/products/purchase-success', [ProductController::class, 'purchaseSuccess'])->name('products.purchase_success');
    Route::get('/products/purchase-cancel', [ProductController::class, 'purchaseCancel'])->name('products.purchase_cancel');
});

Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

Route::middleware('auth')->group(function () {
    Route::get('/product-requests', [ProductRequestController::class, 'index'])->name('product_requests.index');
    Route::get('/product-requests/create', [ProductRequestController::class, 'create'])->name('product_requests.create');
    Route::post('/product-requests', [ProductRequestController::class, 'store'])->name('product_requests.store');
    Route::get('/product-requests/{productRequest}/edit', [ProductRequestController::class, 'edit'])->name('product_requests.edit');
    Route::put('/product-requests/{productRequest}', [ProductRequestController::class, 'update'])->name('product_requests.update');
    Route::get('/product-requests/{productRequest}', [ProductRequestController::class, 'show'])->name('product_requests.show_user');
});

Route::post('/stripe/webhook', [PaymentController::class, 'webhook'])->name('stripe.webhook');

require __DIR__ . '/auth.php';
