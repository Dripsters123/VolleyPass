<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SportDevsService;
use App\Models\VolleyballMatch;
use Illuminate\Http\Request;

class SportDevsController extends Controller
{
    protected $api;

    public function __construct(SportDevsService $api)
    {
        $this->api = $api;
    }

    public function tournaments()
    {
        return response()->json($this->api->tournaments());
    }

    public function liveScores()
    {
        return response()->json($this->api->liveScores());
    }

    public function standings($id)
    {
        return response()->json($this->api->standings($id));
    }

    public function matches($id)
    {
        return response()->json($this->api->matches($id));
    }

    public function upcomingMatches()
    {
        $matches = $this->api->getUpcomingMatches();
        return view('volleyball', compact('matches'));
    }

    public function matchDetails($id)
    {
        $match = $this->api->getMatchById($id);

        return view('matches.show', [
            'match' => $match,
            'arena' => $match['arena'] ?? null,
        ]);
    }

    public function pastMatchDetails($id)
    {
        $match = VolleyballMatch::with('statistics')->findOrFail($id);

        // Decode JSON fields
        $match->arena      = $match->arena ? json_decode($match->arena, true) : null;
        $match->tournament = $match->tournament ? json_decode($match->tournament, true) : null;
        $match->season     = $match->season ? json_decode($match->season, true) : null;
        $match->round      = $match->round ? json_decode($match->round, true) : null;
        $match->league     = $match->league ? json_decode($match->league, true) : null;

        return view('matches.showPast', compact('match'));
    }

    public function apiUpcomingMatches(Request $request)
    {
        $matches = $this->api->getUpcomingMatches();

        // Filters
        if ($request->filled('tournament')) {
            $tournamentFilter = $request->input('tournament');
            $matches = array_filter($matches, fn($m) =>
                isset($m['tournament_name']) &&
                stripos($m['tournament_name'], $tournamentFilter) !== false
            );
        }

        if ($request->filled('home_team')) {
            $homeFilter = $request->input('home_team');
            $matches = array_filter($matches, fn($m) =>
                isset($m['home_team_name']) &&
                stripos($m['home_team_name'], $homeFilter) !== false
            );
        }

        if ($request->filled('away_team')) {
            $awayFilter = $request->input('away_team');
            $matches = array_filter($matches, fn($m) =>
                isset($m['away_team_name']) &&
                stripos($m['away_team_name'], $awayFilter) !== false
            );
        }

        if ($request->filled('date')) {
            $dateFilter = $request->input('date');
            $matches = array_filter($matches, function ($m) use ($dateFilter) {
                if (!isset($m['start_time'])) return false;
                try {
                    return \Carbon\Carbon::parse($m['start_time'])->format('Y-m-d') === $dateFilter;
                } catch (\Exception $e) {
                    return false;
                }
            });
        }

        foreach ($matches as &$match) {
            $match['arena'] = $match['arena'] ?? null;
        }

        return response()->json(array_values($matches));
    }

    public function apiPastMatches(Request $request)
    {
        $matches = $this->api->getPastMatches();

        if ($request->filled('date')) {
            $dateFilter = $request->input('date');
            $matches = array_filter($matches, fn($m) =>
                isset($m['start_time']) &&
                \Carbon\Carbon::parse($m['start_time'])->format('Y-m-d') === $dateFilter
            );
        }

        foreach ($matches as &$match) {
            $match['arena'] = $match['arena'] ?? null;
        }

        return response()->json(array_values($matches));
    }

    public function calendar()
    {
        return view('calendar.index');
    }

    public function calendarFeed()
    {
        $upcoming = $this->api->getUpcomingMatches();
        $past = VolleyballMatch::where('status_type', 'finished')->get();

        $events = [];

        foreach (array_merge($upcoming, $past->toArray()) as $match) {
            $title = $match['home_team_name'] . ' vs ' . $match['away_team_name'];

            if (($match['status_type'] ?? '') === 'finished' &&
                isset($match['home_score'], $match['away_score'])) {
                $title .= " ({$match['home_score']} - {$match['away_score']})";
            }

            $events[] = [
                'id'    => $match['id'],
                'title' => $title,
                'start' => $match['start_time'],
                'url'   => ($match['status_type'] ?? '') === 'finished'
                    ? route('volleyball.past.show', $match['id'])
                    : route('volleyball.show', $match['id']),
                'color' => ($match['status_type'] ?? '') === 'finished' ? '#dc2626' : '#16a34a',
            ];
        }

        return response()->json($events);
    }

    public function apiCalendar(Request $request)
    {
        $past = VolleyballMatch::where('status_type', 'finished')->get();
        $upcoming = VolleyballMatch::where('status_type', 'upcoming')->get();

        $events = [];

        foreach (array_merge($upcoming->toArray(), $past->toArray()) as $match) {
            $title = $match['home_team_name'] . ' vs ' . $match['away_team_name'];

            if ($match['status_type'] === 'finished' &&
                isset($match['home_score'], $match['away_score'])) {
                $title .= " ({$match['home_score']} - {$match['away_score']})";
            }

            $events[] = [
                'id'    => $match['id'],
                'title' => $title,
                'start' => $match['start_time'],
                'end'   => $match['end_time'] ?? null,
                'url'   => $match['status_type'] === 'finished'
                    ? route('volleyball.past.show', $match['id'])
                    : route('volleyball.show', $match['id']),
                'color' => $match['status_type'] === 'finished' ? '#dc2626' : '#16a34a',
            ];
        }

        return response()->json($events);
    }
}
