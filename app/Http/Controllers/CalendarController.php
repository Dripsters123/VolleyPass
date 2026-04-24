<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VolleyballMatch;

class CalendarController extends Controller
{
    public function index()
    {
        return view('calendar.index');
    }

    public function events()
    {
        $matches = VolleyballMatch::where('is_local', true)
            ->orderBy('start_time', 'asc')
            ->get(['id', 'home_team_name', 'away_team_name', 'start_time', 'end_time', 'location', 'ticket_price', 'match_state', 'status_type']);

        $events = $matches->map(function ($match) {
            $status = $match->effective_state ?? 'upcoming';
            return [
                'id'    => $match->id,
                'title' => "{$match->home_team_name} vs {$match->away_team_name}",
                'start' => $match->start_time,
                'end'   => $match->end_time,
                'url'   => route('local.matches.show', $match->id),
                'extendedProps' => [
                    'status'   => $status,
                    'location' => $match->location,
                    'price'    => $match->ticket_price,
                ],
            ];
        });

        return response()->json($events);
    }
}
