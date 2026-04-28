<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Arena;
use App\Models\User;
use App\Models\VolleyballMatch;
use App\Models\Ticket;
use App\Models\MatchRequest;
use App\Models\MatchScoreVerification;
use App\Models\VolleyballMatchSet;
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

        foreach ([2, 4, 6] as $format) {
            if (!$arenas[$format]) {
                $arenas[$format] = Arena::firstOrCreate(
                    [
                        'name' => "Auto Arena ({$format}v{$format})",
                        'user_id' => $admin->id,
                    ],
                    [
                        'description' => 'Auto-generated fallback arena for seeding.',
                        'layout' => [],
                        'elements' => [],
                        'width' => 1000,
                        'height' => 700,
                        'is_public' => false,
                    ]
                );
            }
        }

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

        $playerData = [
            2 => [
                'home' => [
                    ['first_name' => 'Māris',    'last_name' => 'Ozols'],
                    ['first_name' => 'Rihards',  'last_name' => 'Lācis'],
                ],
                'away' => [
                    ['first_name' => 'Kārlis',   'last_name' => 'Vītols'],
                    ['first_name' => 'Andris',   'last_name' => 'Siliņš'],
                ],
            ],
            4 => [
                'home' => [
                    ['first_name' => 'Jānis',    'last_name' => 'Bērziņš'],
                    ['first_name' => 'Toms',     'last_name' => 'Krūmiņš'],
                    ['first_name' => 'Edgars',   'last_name' => 'Kalniņš'],
                    ['first_name' => 'Lauris',   'last_name' => 'Jansons'],
                ],
                'away' => [
                    ['first_name' => 'Sandis',   'last_name' => 'Freibergs'],
                    ['first_name' => 'Artūrs',   'last_name' => 'Ozoliņš'],
                    ['first_name' => 'Gints',    'last_name' => 'Zariņš'],
                    ['first_name' => 'Harijs',   'last_name' => 'Liepa'],
                ],
            ],
            6 => [
                'home' => [
                    ['first_name' => 'Raimonds', 'last_name' => 'Vītols'],
                    ['first_name' => 'Guntars',  'last_name' => 'Siliņš'],
                    ['first_name' => 'Valdis',   'last_name' => 'Puķe'],
                    ['first_name' => 'Atis',     'last_name' => 'Lācis'],
                    ['first_name' => 'Normunds', 'last_name' => 'Ozoliņš'],
                    ['first_name' => 'Imants',   'last_name' => 'Bērziņš'],
                ],
                'away' => [
                    ['first_name' => 'Rolands',  'last_name' => 'Kalniņš'],
                    ['first_name' => 'Mārtiņš',  'last_name' => 'Jansons'],
                    ['first_name' => 'Juris',    'last_name' => 'Freibergs'],
                    ['first_name' => 'Aivars',   'last_name' => 'Krūmiņš'],
                    ['first_name' => 'Uldis',    'last_name' => 'Zariņš'],
                    ['first_name' => 'Aigars',   'last_name' => 'Liepa'],
                ],
            ],
        ];

        $coachData = [
            2 => ['home' => 'Aivars Kalniņš',  'away' => 'Juris Bērziņš'],
            4 => ['home' => 'Uldis Freibergs',  'away' => 'Guntars Ozoliņš'],
            6 => ['home' => 'Māris Vītols',     'away' => 'Edgars Zariņš'],
        ];

        $formats = [2, 4, 6];

        foreach ($formats as $n) {
            $names = $teamNames[$n];
            $arena = $arenas[$n] ?? null;
            
            $adminMatch = VolleyballMatch::create([
                'home_team_name'   => $names['adminMatch']['home'],
                'away_team_name'   => $names['adminMatch']['away'],
                'arena_id'         => $arena->id,
                'players_per_team' => $n,
                'start_time'       => now()->addDays($n)->setTime(18, 0),
                'end_time'         => now()->addDays($n)->setTime(20, 0),
                'is_local'         => true,
                'match_state'      => 'scheduled',
                'ticket_price'     => 12.00 + $n,
                'home_coach'       => $coachData[$n]['home'],
                'away_coach'       => $coachData[$n]['away'],
                'location'         => "Sporta iela 1, Cēsis, LV-4101",
                'judges'           => json_encode(["Jānis Ozoliņš"]),
                'home_players'     => json_encode($playerData[$n]['home']),
                'away_players'     => json_encode($playerData[$n]['away']),
                'home_logo'        => null,
                'away_logo'        => null,
                'home_color'       => '#1f7af0',
                'away_color'       => '#f04f4f',
                'estimated_duration_minutes' => 90,
                'home_score'       => 0,
                'away_score'       => 0,
            ]);

            $this->attachLogos($adminMatch);
            $arena->generateSeatsForMatch($adminMatch, $adminMatch->ticket_price);
            $this->assignRandomTickets($adminMatch, $users);

            
            $userMatch = VolleyballMatch::create([
                'home_team_name'   => $names['userMatch']['home'],
                'away_team_name'   => $names['userMatch']['away'],
                'arena_id'         => $arena->id,
                'players_per_team' => $n,
                'start_time'       => now()->addDays($n + 1)->setTime(17, 0),
                'end_time'         => now()->addDays($n + 1)->setTime(19, 0),
                'is_local'         => true,
                'match_state'      => 'scheduled',
                'ticket_price'     => 10.00 + $n,
                'home_coach'       => $coachData[$n]['home'],
                'away_coach'       => $coachData[$n]['away'],
                'location'         => "Rīgas Sporta Pils, Brnībās iela 57, Rīga, LV-1013",
                'judges'           => json_encode(["Klāvs Lielkalniņš"]),
                'home_players'     => json_encode($playerData[$n]['home']),
                'away_players'     => json_encode($playerData[$n]['away']),
                'home_logo'        => null,
                'away_logo'        => null,
                'home_color'       => '#2b8bf7',
                'away_color'       => '#f05c5c',
                'estimated_duration_minutes' => 90,
                'home_score'       => 0,
                'away_score'       => 0,
            ]);

            $this->attachLogos($userMatch);
            $arena->generateSeatsForMatch($userMatch, $userMatch->ticket_price);
            $this->assignRandomTickets($userMatch, $users);

         
            $completedMatch = VolleyballMatch::create([
                'home_team_name'   => $names['completedMatch']['home'],
                'away_team_name'   => $names['completedMatch']['away'],
                'arena_id'         => $arena->id,
                'players_per_team' => $n,
                'start_time'       => now()->subDays(2)->setTime(18, 0),
                'end_time'         => now()->subDays(2)->setTime(20, 0),
                'is_local'         => true,
                'match_state'      => 'completed',
                'ticket_price'     => 8.00 + $n,
                'home_coach'       => $coachData[$n]['home'],
                'away_coach'       => $coachData[$n]['away'],
                'location'         => "Vidzemes Olimpiskais Centrs, Cimzes iela 2, Valmiera, LV-4201",
                'judges'           => json_encode(["Andris Rozenbergs"]),
                'home_players'     => json_encode($playerData[$n]['home']),
                'away_players'     => json_encode($playerData[$n]['away']),
                'home_logo'        => null,
                'away_logo'        => null,
                'home_score'       => ($completedResult = [[3,0],[3,1],[3,2],[0,3],[1,3],[2,3]][rand(0,5)])[0],
                'away_score'       => $completedResult[1],
                'estimated_duration_minutes' => 90,
            ]);

            $this->attachLogos($completedMatch);
            $arena->generateSeatsForMatch($completedMatch, $completedMatch->ticket_price);
            $this->assignRandomTickets($completedMatch, $users);

            $verifiedSets = match ([$completedMatch->home_score, $completedMatch->away_score]) {
                [3, 0] => [
                    ['home' => 25, 'away' => 18],
                    ['home' => 25, 'away' => 21],
                    ['home' => 25, 'away' => 19],
                ],
                [3, 1] => [
                    ['home' => 25, 'away' => 20],
                    ['home' => 22, 'away' => 25],
                    ['home' => 25, 'away' => 19],
                    ['home' => 25, 'away' => 21],
                ],
                [3, 2] => [
                    ['home' => 25, 'away' => 19],
                    ['home' => 22, 'away' => 25],
                    ['home' => 25, 'away' => 23],
                    ['home' => 21, 'away' => 25],
                    ['home' => 15, 'away' => 12],
                ],
                [0, 3] => [
                    ['home' => 19, 'away' => 25],
                    ['home' => 21, 'away' => 25],
                    ['home' => 18, 'away' => 25],
                ],
                [1, 3] => [
                    ['home' => 22, 'away' => 25],
                    ['home' => 25, 'away' => 23],
                    ['home' => 20, 'away' => 25],
                    ['home' => 18, 'away' => 25],
                ],
                [2, 3] => [
                    ['home' => 25, 'away' => 21],
                    ['home' => 23, 'away' => 25],
                    ['home' => 25, 'away' => 22],
                    ['home' => 18, 'away' => 25],
                    ['home' => 13, 'away' => 15],
                ],
                default => [],
            };

            $pendingSets = match ([$completedMatch->home_score, $completedMatch->away_score]) {
                [3, 0] => [
                    ['home' => 25, 'away' => 23],
                    ['home' => 25, 'away' => 22],
                    ['home' => 25, 'away' => 20],
                ],
                [3, 1] => [
                    ['home' => 25, 'away' => 18],
                    ['home' => 23, 'away' => 25],
                    ['home' => 25, 'away' => 22],
                    ['home' => 25, 'away' => 16],
                ],
                [3, 2] => [
                    ['home' => 25, 'away' => 21],
                    ['home' => 21, 'away' => 25],
                    ['home' => 25, 'away' => 19],
                    ['home' => 23, 'away' => 25],
                    ['home' => 15, 'away' => 10],
                ],
                [0, 3] => [
                    ['home' => 21, 'away' => 25],
                    ['home' => 23, 'away' => 25],
                    ['home' => 19, 'away' => 25],
                ],
                [1, 3] => [
                    ['home' => 20, 'away' => 25],
                    ['home' => 25, 'away' => 23],
                    ['home' => 18, 'away' => 25],
                    ['home' => 22, 'away' => 25],
                ],
                [2, 3] => [
                    ['home' => 25, 'away' => 23],
                    ['home' => 21, 'away' => 25],
                    ['home' => 25, 'away' => 20],
                    ['home' => 20, 'away' => 25],
                    ['home' => 12, 'away' => 15],
                ],
                default => [],
            };

            $pendingHomeWins = 0;
            $pendingAwayWins = 0;
            foreach ($pendingSets as $pendingSet) {
                if (($pendingSet['home'] ?? 0) > ($pendingSet['away'] ?? 0)) {
                    $pendingHomeWins++;
                } elseif (($pendingSet['away'] ?? 0) > ($pendingSet['home'] ?? 0)) {
                    $pendingAwayWins++;
                }
            }

            foreach ($verifiedSets as $setIndex => $setResult) {
                VolleyballMatchSet::create([
                    'match_id' => $completedMatch->id,
                    'set_number' => $setIndex + 1,
                    'home_score' => $setResult['home'],
                    'away_score' => $setResult['away'],
                    'completed' => true,
                ]);
            }

            MatchScoreVerification::create([
                'match_id'     => $completedMatch->id,
                'user_id'      => $users->random()->id,
                'home_score'   => $completedMatch->home_score,
                'away_score'   => $completedMatch->away_score,
                'status'       => 'finalized',
                'approved'     => true,
                'approvals'    => 3,
                'confirmations'=> ['sets' => $verifiedSets],
            ]);

            MatchScoreVerification::create([
                'match_id'     => $completedMatch->id,
                'user_id'      => $users->random()->id,
                'home_score'   => $pendingHomeWins,
                'away_score'   => $pendingAwayWins,
                'status'       => 'pending',
                'approved'     => false,
                'approvals'    => 0,
                'confirmations'=> ['sets' => $pendingSets],
            ]);

           
            MatchRequest::create([
                'user_id'         => $users->random()->id,
                'request_type'    => 'create_match',
                'home_team'       => $names['acceptedReq']['home'],
                'away_team'       => $names['acceptedReq']['away'],
                'start_time'      => now()->addDays($n + 3)->setTime(18, 0),
                'end_time'        => now()->addDays($n + 3)->setTime(20, 0),
                'players_per_team'=> $n,
                'home_players'    => $playerData[$n]['home'],
                'away_players'    => $playerData[$n]['away'],
                'status'          => 'accepted',
                'home_coach'      => $coachData[$n]['home'],
                'away_coach'      => $coachData[$n]['away'],
                'judges'          => ["Klāvs Lielkalniņš"],
                'location'        => "Sporta iela 1, Cēsis, LV-4101",
                'arena_name'      => $arena?->name,
                'arena_layout'    => json_encode($arena?->layout ?? []),
                'arena_elements'  => json_encode($arena?->elements ?? []),
                'arena_width'     => $arena?->width ?? 1000,
                'arena_height'    => $arena?->height ?? 700,
                'ticket_price'    => 10.00 + $n,
            ]);

          
            MatchRequest::create([
                'user_id'         => $users->random()->id,
                'request_type'    => 'create_match',
                'home_team'       => $names['pendingReq']['home'],
                'away_team'       => $names['pendingReq']['away'],
                'start_time'      => now()->addDays($n + 4)->setTime(18, 0),
                'end_time'        => now()->addDays($n + 4)->setTime(20, 0),
                'players_per_team'=> $n,
                'home_players'    => $playerData[$n]['home'],
                'away_players'    => $playerData[$n]['away'],
                'status'          => 'pending',
                'home_coach'      => $coachData[$n]['home'],
                'away_coach'      => $coachData[$n]['away'],
                'judges'          => ["Anna Ozoliņa"],
                'location'        => "Rēzeknes Sporta Centrs, Atbrīvošanas al. 83, Rēzekne, LV-4601",
                'arena_name'      => $arena?->name,
                'arena_layout'    => json_encode($arena?->layout ?? []),
                'arena_elements'  => json_encode($arena?->elements ?? []),
                'arena_width'     => $arena?->width ?? 1000,
                'arena_height'    => $arena?->height ?? 700,
                'ticket_price'    => 8.00 + $n,
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
                'score_home'   => 3,
                'score_away'   => 1,
                'status'       => 'pending',
                'home_coach'   => $adminMatch->home_coach,
                'away_coach'   => $adminMatch->away_coach,
                'judges'       => $adminMatch->judges,
                'location'     => $adminMatch->location,
                'home_players' => json_encode($adminMatch->home_players ?? []),
                'away_players' => json_encode($adminMatch->away_players ?? []),
            ]);

            MatchScoreVerification::create([
                'match_id'     => $adminMatch->id,
                'user_id'      => $users->random()->id,
                'home_score'   => 3,
                'away_score'   => 1,
                'status'       => 'pending',
                'approved'     => false,
                'approvals'    => 0,
                'confirmations'=> [
                    'sets' => [
                        ['home' => 25, 'away' => 20],
                        ['home' => 23, 'away' => 25],
                        ['home' => 27, 'away' => 25],
                        ['home' => 25, 'away' => 18],
                    ],
                ],
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
