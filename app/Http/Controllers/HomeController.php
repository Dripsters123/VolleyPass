<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VolleyballMatch;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    // Rāda galveno lapu ar nākamajiem mačiem un populārāko maèu
    public function index()
    {
        $matchesCollection = VolleyballMatch::where('is_local', true)
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->take(12)
            ->get();

        
        $matches = $matchesCollection->map(function ($m) {
            return [
                'id' => $m->id,
                'home_team_name' => $m->home_team_name,
                'away_team_name' => $m->away_team_name,
                'tournament_name' => $m->tournament ?? ($m->tournament_name ?? null),
                'start_time' => $m->start_time?->toDateTimeString(),
                'arena' => $m->arena ?? null,
                'ticket_price' => (float) ($m->ticket_price ?? 0),
            ];
        })->toArray();

        $popularData = Ticket::select('event_id', DB::raw('COUNT(*) as sold'))
            ->whereIn('event_id', $matchesCollection->pluck('id')->all())
            ->groupBy('event_id')
            ->orderByDesc('sold')
            ->first();

        $popular = null;
        $popularSold = 0;

        if ($popularData) {
            $popularMatch = $matchesCollection->firstWhere('id', $popularData->event_id);
            if ($popularMatch) {
                $popular = [
                    'id' => $popularMatch->id,
                    'home_team_name' => $popularMatch->home_team_name,
                    'away_team_name' => $popularMatch->away_team_name,
                    'tournament_name' => $popularMatch->tournament ?? ($popularMatch->tournament_name ?? null),
                    'start_time' => $popularMatch->start_time?->toDateTimeString(),
                    'arena' => $popularMatch->arena ?? null,
                ];
                $popularSold = (int) $popularData->sold;
            }
        } else {
        // Ja nav pārdotu biļešu — ņem pirmo maèu kā populāro
            $first = $matchesCollection->first();
            if ($first) {
                $popular = [
                    'id' => $first->id,
                    'home_team_name' => $first->home_team_name,
                    'away_team_name' => $first->away_team_name,
                    'tournament_name' => $first->tournament ?? ($first->tournament_name ?? null),
                    'start_time' => $first->start_time?->toDateTimeString(),
                    'arena' => $first->arena ?? null,
                ];
                $popularSold = 0;
            }
        }

        return view('pages.home', compact('matches', 'popular', 'popularSold'));
    }
}
