<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\SportDevsService;

class HomeController extends Controller
{
    protected $api;

    public function __construct(SportDevsService $api)
    {
        $this->api = $api;
    }

   // app/Http/Controllers/HomeController.php
public function index()
{
    if (auth()->check()) {
        return redirect()->route('dashboard'); // redirect logged-in users
    }

    $matches = $this->api->getUpcomingMatches();

    $popularMatch = Ticket::selectRaw('event_id, COUNT(*) as sold')
        ->groupBy('event_id')
        ->orderByDesc('sold')
        ->first();

    $popular = null;
    $popularSold = 0;

    if ($popularMatch) {
        $popularSold = (int) $popularMatch->sold;
        $popular = collect($matches)->firstWhere('id', $popularMatch->event_id);
    }

    return view('pages.home', [
        'matches' => $matches,
        'popular' => $popular,
        'popularSold' => $popularSold,
    ]);
}

}
