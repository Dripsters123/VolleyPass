<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Arena;
use App\Models\User;
use App\Models\VolleyballMatch;
use App\Models\Ticket;
use App\Models\MatchRequest;
use App\Models\MatchScoreVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocalMatchSeeder extends Seeder
{
    public function run(): void
    {
        
        $admin = User::firstWhere('role', 'admin') ?? User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'password' => bcrypt('AdminPassword123!'),
        ]);

        $users = User::factory()->count(6)->create();
        $users->push($admin);

        // Load default arena layouts seeded by ArenaSeeder
        $arenas = [
            2 => Arena::where('name', 'Pludmales volejbols (2v2)')->first(),
            4 => Arena::where('name', 'Kompaktā halle (4v4)')->first(),
            6 => Arena::where('name', 'Olimpijas halle (6v6)')->first(),
        ];

        $teamNames = [
            2 => [
                'adminMatch'     => ['home' => 'Jūrmalas Volejs',   'away' => 'Pludmales Vilki'],
                'userMatch'      => ['home' => 'Krišjānis & Māris', 'away' => 'Toms & Jānis'],
                'completedMatch' => ['home' => 'Lelde & Elīna',     'away' => 'Aija & Santa'],
                'acceptedReq'    => ['home' => 'Piņķu Pāris',       'away' => 'Jūrmalas Duets'],
                'pendingReq'     => ['home' => 'Siguldas Duo',       'away' => 'Ādažu Pāris'],
            ],
            4 => [
                'adminMatch'     => ['home' => 'Valmieras Lauvas',  'away' => 'Liepājas Vēja'],
                'userMatch'      => ['home' => 'Ogres Tigri',       'away' => 'Jēkabpils Pumas'],
                'completedMatch' => ['home' => 'Rēzeknes Lāči',     'away' => 'Ventspils Stars'],
                'acceptedReq'    => ['home' => 'Tukuma Viesuļi',    'away' => 'Dobeles Vilki'],
                'pendingReq'     => ['home' => 'Kandavas Ērgļi',    'away' => 'Saldus Vētras'],
            ],
            6 => [
                'adminMatch'     => ['home' => 'Rīgas Pērkons',     'away' => 'Cēsu Ērgļi'],
                'userMatch'      => ['home' => 'Valmieras Vilki',   'away' => 'Jūrmalas Vilnis'],
                'completedMatch' => ['home' => 'Daugavas Ērgļi',    'away' => 'Liepājas Vēja'],
                'acceptedReq'    => ['home' => 'Rīgas Jūrnieki',    'away' => 'Jelgavas Lāči'],
                'pendingReq'     => ['home' => 'Bauskas Viesuli',   'away' => 'Kuldīgas Vilki'],
            ],
        ];

        $formats = [2, 4, 6];

        foreach ($formats as $n) {
            $names = $teamNames[$n];
            $arena = $arenas[$n] ?? null;
            
            $adminMatch = VolleyballMatch::create([
                'home_team_name'   => $names['adminMatch']['home'],
                'away_team_name'   => $names['adminMatch']['away'],
                'arena_id'         => $arena?->id,
                'players_per_team' => $n,
                'start_time'       => now()->addDays($n)->setTime(18, 0),
                'end_time'         => now()->addDays($n)->setTime(20, 0),
                'is_local'         => true,
                'status_type'      => 'scheduled',
                'match_state'      => 'scheduled',
                'ticket_price'     => 12.00 + $n,
                'home_coach'       => "Coach H{$n}",
                'away_coach'       => "Coach A{$n}",
                'location'         => "Cēsis, Latvia",
                'judges'           => json_encode(["Judge " . Str::upper(Str::random(3))]),
                'home_players'     => json_encode(array_map(fn($i) => [
                    'first_name' => 'H' . ($i + 1),
                    'last_name'  => 'Player' . ($i + 1),
                ], range(0, $n - 1))),
                'away_players'     => json_encode(array_map(fn($i) => [
                    'first_name' => 'A' . ($i + 1),
                    'last_name'  => 'Player' . ($i + 1),
                ], range(0, $n - 1))),
                'home_logo'        => null,
                'away_logo'        => null,
                'home_color'       => '#1f7af0',
                'away_color'       => '#f04f4f',
                'estimated_duration_minutes' => 90,
                'home_score'       => 0,
                'away_score'       => 0,
            ]);

            $this->attachLogos($adminMatch);
            if ($arena) {
                $arena->generateSeatsForMatch($adminMatch, $adminMatch->ticket_price);
            } else {
                $this->generateSeats($adminMatch);
            }
            $this->assignRandomTickets($adminMatch, $users);

            
            $userMatch = VolleyballMatch::create([
                'home_team_name'   => $names['userMatch']['home'],
                'away_team_name'   => $names['userMatch']['away'],
                'arena_id'         => $arena?->id,
                'players_per_team' => $n,
                'start_time'       => now()->addDays($n + 1)->setTime(17, 0),
                'end_time'         => now()->addDays($n + 1)->setTime(19, 0),
                'is_local'         => true,
                'status_type'      => 'scheduled',
                'match_state'      => 'scheduled',
                'ticket_price'     => 10.00 + $n,
                'home_coach'       => "UserCoach H{$n}",
                'away_coach'       => "UserCoach A{$n}",
                'location'         => "Rīga, Latvia",
                'judges'           => json_encode(["Judge " . Str::upper(Str::random(3))]),
               
                'home_players'     => json_encode(array_map(fn($i) => [
                    'first_name' => 'H' . ($i + 1),
                    'last_name'  => 'Player' . ($i + 1),
                ], range(0, $n - 1))),
                'away_players'     => json_encode(array_map(fn($i) => [
                    'first_name' => 'A' . ($i + 1),
                    'last_name'  => 'Player' . ($i + 1),
                ], range(0, $n - 1))),
                'home_logo'        => null,
                'away_logo'        => null,
                'home_color'       => '#2b8bf7',
                'away_color'       => '#f05c5c',
                'estimated_duration_minutes' => 90,
                'home_score'       => 0,
                'away_score'       => 0,
            ]);

            $this->attachLogos($userMatch);
            if ($arena) {
                $arena->generateSeatsForMatch($userMatch, $userMatch->ticket_price);
            } else {
                $this->generateSeats($userMatch);
            }
            $this->assignRandomTickets($userMatch, $users);

         
            $completedMatch = VolleyballMatch::create([
                'home_team_name'   => $names['completedMatch']['home'],
                'away_team_name'   => $names['completedMatch']['away'],
                'arena_id'         => $arena?->id,
                'players_per_team' => $n,
                'start_time'       => now()->subDays(2)->setTime(18, 0),
                'end_time'         => now()->subDays(2)->setTime(20, 0),
                'is_local'         => true,
                'status_type'      => 'completed',
                'match_state'      => 'completed',
                'ticket_price'     => 8.00 + $n,
                'home_coach'       => "FinishCoach H{$n}",
                'away_coach'       => "FinishCoach A{$n}",
                'location'         => "Valmiera, Latvia",
                'judges'           => json_encode(["Judge " . Str::upper(Str::random(3))]),
                'home_players'     => json_encode(array_map(fn($i) => [
                    'first_name' => 'H' . ($i + 1),
                    'last_name'  => 'Player' . ($i + 1),
                ], range(0, $n - 1))),
                'away_players'     => json_encode(array_map(fn($i) => [
                    'first_name' => 'A' . ($i + 1),
                    'last_name'  => 'Player' . ($i + 1),
                ], range(0, $n - 1))),
                'home_logo'        => null,
                'away_logo'        => null,
                'home_score'       => ($completedResult = [[3,0],[3,1],[3,2],[0,3],[1,3],[2,3]][rand(0,5)])[0],
                'away_score'       => $completedResult[1],
                'estimated_duration_minutes' => 90,
            ]);

            $this->attachLogos($completedMatch);
            if ($arena) {
                $arena->generateSeatsForMatch($completedMatch, $completedMatch->ticket_price);
            } else {
                $this->generateSeats($completedMatch);
            }
            $this->assignRandomTickets($completedMatch, $users);

            MatchScoreVerification::create([
                'match_id'     => $completedMatch->id,
                'user_id'      => $users->random()->id,
                'home_score'   => $completedMatch->home_score,
                'away_score'   => $completedMatch->away_score,
                'status'       => 'finalized',
                'approved'     => true,
                'approvals'    => 3,
                'confirmations'=> json_encode([]),
            ]);

            MatchScoreVerification::create([
                'match_id'     => $completedMatch->id,
                'user_id'      => $users->random()->id,
                'home_score'   => max(0, $completedMatch->home_score - 1),
                'away_score'   => max(0, $completedMatch->away_score - 1),
                'status'       => 'pending',
                'approved'     => false,
                'approvals'    => 0,
                'confirmations'=> json_encode([]),
            ]);

           
            MatchRequest::create([
                'user_id'         => $users->random()->id,
                'request_type'    => 'create_match',
                'home_team'       => $names['acceptedReq']['home'],
                'away_team'       => $names['acceptedReq']['away'],
                'start_time'      => now()->addDays($n + 3)->setTime(18, 0),
                'end_time'        => now()->addDays($n + 3)->setTime(20, 0),
                'players_per_team'=> $n,
                'home_players'    => json_encode(array_map(fn($i) => [
                    'first_name' => 'H' . ($i + 1),
                    'last_name'  => 'Accepted' . ($i + 1),
                ], range(0, $n - 1))),
                'away_players'    => json_encode(array_map(fn($i) => [
                    'first_name' => 'A' . ($i + 1),
                    'last_name'  => 'Accepted' . ($i + 1),
                ], range(0, $n - 1))),
                'status'          => 'accepted',
                'home_coach'      => "ReqCoach H{$n}",
                'away_coach'      => "ReqCoach A{$n}",
                'judges'          => json_encode(["Judge " . Str::upper(Str::random(3))]),
                'location'        => "Cēsis, Latvia",
            ]);

          
            MatchRequest::create([
                'user_id'         => $users->random()->id,
                'request_type'    => 'create_match',
                'home_team'       => $names['pendingReq']['home'],
                'away_team'       => $names['pendingReq']['away'],
                'start_time'      => now()->addDays($n + 4)->setTime(18, 0),
                'end_time'        => now()->addDays($n + 4)->setTime(20, 0),
                'players_per_team'=> $n,
                'home_players'    => json_encode([]),
                'away_players'    => json_encode([]),
                'status'          => 'pending',
                'home_coach'      => 'TBD',
                'away_coach'      => 'TBD',
                'judges'          => json_encode([]),
                'location'        => "Rēzekne, Latvia",
            ]);

            MatchRequest::create([
                'user_id'      => $users->random()->id,
                'request_type' => 'score_update',
                'home_team'    => $adminMatch->home_team_name,
                'away_team'    => $adminMatch->away_team_name,
                'start_time'   => $adminMatch->start_time,
                'end_time'     => $adminMatch->end_time,
                'players_per_team' => $adminMatch->players_per_team,
                'match_id'     => $adminMatch->id,
                'score_home'   => rand(0, 20),
                'score_away'   => rand(0, 20),
                'status'       => 'pending',
                'home_coach'   => $adminMatch->home_coach,
                'away_coach'   => $adminMatch->away_coach,
                'judges'       => $adminMatch->judges,
                'location'     => $adminMatch->location,
                'home_players' => json_encode($adminMatch->home_players ?? []),
                'away_players' => json_encode($adminMatch->away_players ?? []),
            ]);

            MatchRequest::create([
                'user_id'      => $users->random()->id,
                'request_type' => 'score_update',
                'home_team'    => $completedMatch->home_team_name,
                'away_team'    => $completedMatch->away_team_name,
                'start_time'   => $completedMatch->start_time,
                'end_time'     => $completedMatch->end_time,
                'players_per_team' => $completedMatch->players_per_team,
                'match_id'     => $completedMatch->id,
                'score_home'   => $completedMatch->home_score,
                'score_away'   => $completedMatch->away_score,
                'status'       => 'accepted',
                'home_coach'   => $completedMatch->home_coach,
                'away_coach'   => $completedMatch->away_coach,
                'judges'       => $completedMatch->judges,
                'location'     => $completedMatch->location,
                'home_players' => json_encode($completedMatch->home_players ?? []),
                'away_players' => json_encode($completedMatch->away_players ?? []),
            ]);
        }
    }

    private function generateSeats(VolleyballMatch $match): void
    {
        $rows = 6;
        $cols = 12;
        $sideRows = 12;
        $sideCols = 4;
        $now = now();
        $toInsert = [];

        $stands = [
            ['label' => 'Augšējā tribīne', 'dir' => 'row', 'rows' => $rows, 'cols' => $cols],
            ['label' => 'Apakšējā tribīne', 'dir' => 'row', 'rows' => $rows, 'cols' => $cols],
            ['label' => 'Kreisā tribīne', 'dir' => 'col', 'rows' => $sideRows, 'cols' => $sideCols],
            ['label' => 'Labā tribīne', 'dir' => 'col', 'rows' => $sideRows, 'cols' => $sideCols],
        ];

        foreach ($stands as $stand) {
            $label = $stand['label'];
            $dir = $stand['dir'];
            $rMax = (int)$stand['rows'];
            $cMax = (int)$stand['cols'];

            if ($dir === 'row') {
                for ($r = 1; $r <= $rMax; $r++) {
                    for ($c = 1; $c <= $cMax; $c++) {
                        $slug = Str::slug($label);
                        $seatKey = "{$slug}-{$r}-{$c}";
                        $toInsert[] = [
                            'match_id' => $match->id,
                            'seat_number' => $seatKey,
                            'side' => $label,
                            'row' => $r,
                            'number' => $c,
                            'price' => $match->ticket_price ?? 10.00,
                            'ticket_id' => null,
                            'user_id' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            } else {
                for ($c = 1; $c <= $cMax; $c++) {
                    for ($r = 1; $r <= $rMax; $r++) {
                        $slug = Str::slug($label);
                        $seatKey = "{$slug}-{$r}-{$c}";
                        $toInsert[] = [
                            'match_id' => $match->id,
                            'seat_number' => $seatKey,
                            'side' => $label,
                            'row' => $r,
                            'number' => $c,
                            'price' => $match->ticket_price ?? 10.00,
                            'ticket_id' => null,
                            'user_id' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }
        }

        foreach (array_chunk($toInsert, 250) as $chunk) {
            DB::table('seats')->insertOrIgnore($chunk);
        }
    }

    private function assignRandomTickets(VolleyballMatch $match, $users): void
    {
        foreach ($users as $user) {
            $numSeats = rand(0, 2);
            if ($numSeats <= 0) continue;

            $ticket = Ticket::create([
                'user_id' => $user->id,
                'event_id' => $match->id,
                'ticket_type' => 'seat',
                'quantity' => $numSeats,
                'amount_paid' => ($match->ticket_price ?? 10.00) * $numSeats,
                'currency' => 'EUR',
                'status' => 'paid',
                'stripe_email' => $user->email,
                'stripe_payment_intent' => 'seeded-intent-' . uniqid(),
            ]);

            $availableSeats = DB::table('seats')
                ->where('match_id', $match->id)
                ->whereNull('ticket_id')
                ->inRandomOrder()
                ->limit($numSeats)
                ->get();

            foreach ($availableSeats as $s) {
                DB::table('seats')->where('id', $s->id)->update([
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function attachLogos(VolleyballMatch $match): void
    {
        try {
            $homeInitials = $this->teamInitials($match->home_team_name);
            $awayInitials = $this->teamInitials($match->away_team_name);

            $homeColor = $match->home_color ?? '#1f7af0';
            $awayColor = $match->away_color ?? '#f04f4f';

            $homeSvg = $this->generateSimpleSvg($homeInitials, $homeColor);
            $awaySvg = $this->generateSimpleSvg($awayInitials, $awayColor);

            $homePath = 'match_logos/home_' . $match->id . '_' . Str::slug($homeInitials) . '.svg';
            $awayPath = 'match_logos/away_' . $match->id . '_' . Str::slug($awayInitials) . '.svg';

            Storage::disk('public')->put($homePath, $homeSvg);
            Storage::disk('public')->put($awayPath, $awaySvg);

            $match->update([
                'home_logo' => $homePath,
                'away_logo' => $awayPath,
            ]);
        } catch (\Throwable $e) {
            \Log::warning("Logo generation failed for match {$match->id}: " . $e->getMessage());
        }
    }

    private function teamInitials(string $name): string
    {
        $parts = preg_split('/\s+|[^A-Za-z0-9]+/u', trim($name));
        $initials = '';
        foreach ($parts as $p) {
            if ($p === '') continue;
            $initials .= mb_substr($p, 0, 1);
            if (mb_strlen($initials) >= 3) break;
        }
        return mb_strtoupper($initials ?: 'T');
    }

    private function generateSimpleSvg(string $text, string $color): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200">
  <rect width="200" height="200" rx="20" fill="{$color}"/>
  <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
        fill="#fff" font-family="Arial, sans-serif" font-size="64" font-weight="bold">
    {$text}
  </text>
</svg>
SVG;
    }
}
