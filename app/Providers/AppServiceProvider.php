<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\View;
use App\Models\Wallet;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
public function boot()
{
    // Make wallet available to all views
    View::composer('layouts.navigation', function ($view) {
        $wallet = null;
        if(auth()->check()) {
            $wallet = Wallet::where('user_id', auth()->id())->first();
        }
        $view->with('wallet', $wallet);
    });
}
}
