<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\VolleyballMatchStatistic;
use App\Models\VolleyballMatch;

class SportDevsService
{
    protected string $url;
    protected string $key;

    public function __construct()
    {
        $this->url = rtrim(config('services.sportdevs.url', 'https://volleyball.sportdevs.com'), '/');
        $this->key = (string) config('services.sportdevs.key');
    }

    private function request(string $endpoint, array $params = [])
    {
        $response = Http::baseUrl($this->url)
            ->withToken($this->key)
            ->acceptJson()
            ->get($endpoint, $params);

        if ($response->failed()) {
            Log::error('SportDevs API error', [
                'endpoint' => $endpoint,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);

            abort($response->status(), $response->body());
        }

        return $response->json();
    }

    public function tournaments()
    {
        return $this->request('tournaments');
    }

    public function standings($tournamentId)
    {
        return $this->request("tournaments/{$tournamentId}/standings");
    }

    public function matches($tournamentId)
    {
        return $this->request("tournaments/{$tournamentId}/matches");
    }

    public function getUpcomingMatches()
    {
        return Cache::remember('sportdevs_upcoming', 360, function () {
            $today = now()->format('Y-m-d');

            $matches = $this->request('matches', [
                'start_time'  => "gte.$today",
                'status_type' => 'eq.upcoming',
                'limit'       => 30,
                'lang'        => 'en',
            ]);

            foreach ($matches as &$match) {
                if (!empty($match['arena_id'])) {
                    $match['arena'] = $this->getArenaById($match['arena_id']);
                }
            }

            return $matches;
        });
    }

    public function getPastMatches()
    {
        return Cache::remember('sportdevs_past', 60, function () {
            $from = now()->subYear()->format('Y-m-d');
            $to   = now()->format('Y-m-d');

            $matches = $this->request('matches', [
                'start_time'  => "gte.$from",
                'start_time'  => "lt.$to",
                'status_type' => 'eq.finished',
                'limit'       => 25,
                'lang'        => 'en',
            ]);

            foreach ($matches as &$match) {
                if (!empty($match['arena_id'])) {
                    $match['arena'] = $this->getArenaById($match['arena_id']);
                }
            }

            return $matches;
        });
    }

    public function getMatchById($id)
    {
        $results = $this->request('matches', [
            'id' => "eq.$id"
        ]);

        $match = $results[0] ?? null;

        if ($match && !empty($match['arena_id'])) {
            $match['arena'] = $this->getArenaById($match['arena_id']);
        }

        return $match;
    }

    public function getArenaById($id)
    {
        $response = $this->request('arenas', [
            'id'   => "eq.$id",
            'lang' => 'en',
            'limit'=> 1
        ]);

        return $response[0] ?? null;
    }

    public function getMatchStatistics(int $matchId, int $limit = 50, int $offset = 0): array
    {
        $response = $this->request('matches-statistics', [
            'match_id' => "eq.$matchId",
            'limit'    => $limit,
            'offset'   => $offset,
            'lang'     => 'en',
        ]);

        // Fix: Statistics are nested inside ["statistics"] key
        if (is_array($response) && isset($response[0]['statistics'])) {
            return $response[0]['statistics'];
        }

        return [];
    }

    public function syncPastMatchesToDb()
    {
        $matches = $this->getPastMatches();

        foreach ($matches as $match) {
            $dbMatch = VolleyballMatch::updateOrCreate(
                ['id' => $match['id']],
                [
                    'home_team_name' => $match['home_team_name'] ?? null,
                    'away_team_name' => $match['away_team_name'] ?? null,
                    'status_type'    => $match['status_type'] ?? null,
                    'start_time'     => $match['start_time'] ?? null,
                    'end_time'       => $match['end_time'] ?? null,
                    'home_score'     => $match['home_team_score']['current'] ?? null,
                    'away_score'     => $match['away_team_score']['current'] ?? null,
                    'arena'          => isset($match['arena']) ? json_encode($match['arena']) : null,
                    'tournament'     => isset($match['tournament_name']) ? json_encode([
                        'id' => $match['tournament_id'] ?? null,
                        'name' => $match['tournament_name'] ?? null,
                    ]) : null,
                    'season'         => isset($match['season_name']) ? json_encode([
                        'id' => $match['season_id'] ?? null,
                        'name' => $match['season_name'] ?? null,
                    ]) : null,
                    'round'          => isset($match['round']) ? json_encode($match['round']) : null,
                    'league'         => isset($match['league_name']) ? json_encode([
                        'id' => $match['league_id'] ?? null,
                        'name' => $match['league_name'] ?? null,
                    ]) : null,
                ]
            );

            // Sync statistics
            $statistics = $this->getMatchStatistics($match['id']);
            foreach ($statistics as $stat) {
                if (!isset($stat['type'])) continue;

                VolleyballMatchStatistic::updateOrCreate(
                    [
                        'match_id' => $dbMatch->id,
                        'type'     => $stat['type'],
                        'period'   => $stat['period'] ?? 'ALL',
                    ],
                    [
                        'category'  => $stat['category'] ?? null,
                        'home_team' => $stat['home_team'] ?? null,
                        'away_team' => $stat['away_team'] ?? null,
                    ]
                );
            }
        }
    }

    public function syncUpcomingMatchesToDb()
    {
        $matches = $this->getUpcomingMatches();

        foreach ($matches as $match) {
            VolleyballMatch::updateOrCreate(
                ['id' => $match['id']],
                [
                    'home_team_name' => $match['home_team_name'] ?? null,
                    'away_team_name' => $match['away_team_name'] ?? null,
                    'status_type'    => $match['status_type'] ?? null,
                    'start_time'     => $match['start_time'] ?? null,
                    'end_time'       => $match['end_time'] ?? null,
                    'home_score'     => $match['home_score'] ?? null,
                    'away_score'     => $match['away_score'] ?? null,
                    'arena'          => isset($match['arena']) ? json_encode($match['arena']) : null,
                ]
            );
        }
    }
}
