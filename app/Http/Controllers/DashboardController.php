<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\VolleyballMatch;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();

        
        $recentPurchases = Ticket::with('event')
            ->where('user_id', $user?->id)
            ->latest()
            ->take(5)
            ->get();

       
        $recentMatches = collect(session('recent_matches', []))
            ->unique()
            ->values()
            ->take(5);

        
        $upcomingMatchesQuery = VolleyballMatch::query()
            ->where('is_local', true)
            ->where('start_time', '>=', now())
            ->orderBy('start_time');

        $upcomingMatches = $upcomingMatchesQuery->take(5)->get()->map(function ($m) {
            return [
                'id' => $m->id,
                'home_team_name' => $m->home_team_name,
                'away_team_name' => $m->away_team_name,
                'start_time' => $m->start_time?->toDateTimeString(),
                'ticket_price' => (float) ($m->ticket_price ?? 0),
            ];
        })->toArray();

        $recommendations = VolleyballMatch::where('is_local', true)
            ->where('start_time', '>=', now())
            ->inRandomOrder()
            ->take(4)
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'home_team_name' => $m->home_team_name,
                    'away_team_name' => $m->away_team_name,
                    'start_time' => $m->start_time?->toDateTimeString(),

                ];
            })->toArray();

        return view('dashboard', compact('recentPurchases', 'recentMatches', 'upcomingMatches', 'recommendations'));
    }
}
