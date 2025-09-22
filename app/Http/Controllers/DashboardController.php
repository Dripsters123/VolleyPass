<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Services\SportDevsService;

class DashboardController extends Controller
{
    protected $api;

    public function __construct(SportDevsService $api)
    {
        $this->api = $api;
    }

   public function index(Request $request)
{
    // 1. Recent purchases
    $recentPurchases = Ticket::with('event')
        ->where('user_id', auth()->id())
        ->latest()
        ->take(5)
        ->get();

    // 2. Recently viewed matches
    $recentMatches = collect(session('recent_matches', []))
        ->unique()
        ->take(5);

    // 3. Upcoming matches
    $upcomingMatches = array_slice($this->api->getUpcomingMatches(), 0, 5);

    // 4. Ieteikumi (recommendations)
    // Idea: suggest upcoming matches where at least one team matches teams from past purchases
    $favoriteTeams = $recentPurchases->pluck('event.home_team')
        ->merge($recentPurchases->pluck('event.away_team'))
        ->unique()
        ->filter();

    $recommendations = collect($upcomingMatches)->filter(function ($match) use ($favoriteTeams) {
        return $favoriteTeams->contains($match['home_team_name']) ||
               $favoriteTeams->contains($match['away_team_name']);
    })->take(3);

    return view('dashboard', compact('recentPurchases', 'recentMatches', 'upcomingMatches', 'recommendations'));
}

}
