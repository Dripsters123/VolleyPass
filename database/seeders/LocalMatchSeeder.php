<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\VolleyballMatch;
use App\Models\Ticket;
use App\Models\MatchRequest;
use App\Models\MatchScoreVerification;
use App\Models\MatchMedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LocalMatchSeeder extends Seeder
{
    public function run(): void
    {
        // -----------------------------
        // Users
        // -----------------------------
        $admin = User::firstWhere('role', 'admin') ?? User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'password' => bcrypt('AdminPassword123!'),
        ]);

        // create some normal users
        $users = User::factory()->count(6)->create();
        // ensure admin is present in selection pool if needed
        $users->push($admin);

        // -----------------------------
        // Formats to seed (2v2, 4v4, 6v6)
        // -----------------------------
        $formats = [2, 4, 6];

        foreach ($formats as $n) {
            // Admin-created match (scheduled)
            $adminMatch = VolleyballMatch::create([
                'home_team_name'     => "LocalHome {$n}v{$n}",
                'away_team_name'     => "LocalAway {$n}v{$n}",
                'players_per_team'   => $n,
                'start_time'         => now()->addDays($n)->setTime(18, 0),
                'end_time'           => now()->addDays($n)->setTime(20, 0),
                'is_local'           => true,
                'status_type'        => 'scheduled',
                'match_state'        => 'scheduled',
                'ticket_price'       => 12.00 + $n,
                'home_coach'         => "Coach H{$n}",
                'away_coach'         => "Coach A{$n}",
                'location'           => "Cēsis, Latvia",
                'judges'             => json_encode(["Judge " . Str::upper(Str::random(3))]),
                'home_players'       => json_encode(array_map(fn($i) => [
                    'first_name' => 'H' . ($i+1),
                    'last_name'  => 'Player' . ($i+1),
                ], range(0, $n-1))),
                'away_players'       => json_encode(array_map(fn($i) => [
                    'first_name' => 'A' . ($i+1),
                    'last_name'  => 'Player' . ($i+1),
                ], range(0, $n-1))),
                'home_logo'          => null,
                'away_logo'          => null,
                'home_color'         => '#1f7af0',
                'away_color'         => '#f04f4f',
                'estimated_duration_minutes' => 90,
            ]);

            // create logos (svg) and save to public disk
            $this->attachLogos($adminMatch);

            $this->generateSeats($adminMatch);
            $this->assignRandomTickets($adminMatch, $users);

            // User-created match (scheduled)
            $userMatch = VolleyballMatch::create([
                'home_team_name'   => 'Team ' . Str::upper(Str::random(3)) . " {$n}v{$n}",
                'away_team_name'   => 'Team ' . Str::upper(Str::random(3)) . " {$n}v{$n}",
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
                'home_players'     => null,
                'away_players'     => null,
                'home_logo'        => null,
                'away_logo'        => null,
                'home_color'       => '#2b8bf7',
                'away_color'       => '#f05c5c',
                'estimated_duration_minutes' => 90,
            ]);

            // attach logos for user match
            $this->attachLogos($userMatch);

            $this->generateSeats($userMatch);
            $this->assignRandomTickets($userMatch, $users);

            // -----------------------------
            // Completed match (with score + verifications)
            // -----------------------------
            $completedMatch = VolleyballMatch::create([
                'home_team_name'   => "CompletedHome {$n}v{$n}",
                'away_team_name'   => "CompletedAway {$n}v{$n}",
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
                'home_players'     => null,
                'away_players'     => null,
                'home_logo'        => null,
                'away_logo'        => null,
                'home_score'       => rand(0, 100),
                'away_score'       => rand(0, 100),
                'estimated_duration_minutes' => 90,
            ]);

            $this->attachLogos($completedMatch);
            $this->generateSeats($completedMatch);
            $this->assignRandomTickets($completedMatch, $users);

            // create a couple of verification/score requests for this completed match (one finalized, one pending)
            MatchScoreVerification::create([
                'match_id' => $completedMatch->id,
                'user_id' => $users->random()->id,
                'home_score' => $completedMatch->home_score,
                'away_score' => $completedMatch->away_score,
                'status' => 'finalized',
                'approved' => true,
                'approvals' => 3,
                'confirmations' => json_encode([]),
            ]);

            MatchScoreVerification::create([
                'match_id' => $completedMatch->id,
                'user_id' => $users->random()->id,
                'home_score' => max(0, $completedMatch->home_score - 1),
                'away_score' => max(0, $completedMatch->away_score - 1),
                'status' => 'pending',
                'approved' => false,
                'approvals' => 0,
                'confirmations' => json_encode([]),
            ]);

            // -----------------------------
            // Accepted MatchRequest (example)
            // -----------------------------
            MatchRequest::create([
                'user_id' => $users->random()->id,
                'request_type' => 'create_match',
                'home_team' => "AcceptedHome {$n}v{$n}",
                'away_team' => "AcceptedAway {$n}v{$n}",
                'start_time' => now()->addDays($n + 3)->setTime(18, 0),
                'end_time' => now()->addDays($n + 3)->setTime(20, 0),
                'players_per_team' => $n,
                'home_players' => json_encode(array_map(fn($i) => [
                    'first_name' => 'H' . ($i + 1),
                    'last_name'  => 'Accepted' . ($i + 1),
                ], range(0, $n - 1))),
                'away_players' => json_encode(array_map(fn($i) => [
                    'first_name' => 'A' . ($i + 1),
                    'last_name'  => 'Accepted' . ($i + 1),
                ], range(0, $n - 1))),
                'status' => 'accepted',
                'home_coach' => "ReqCoach H{$n}",
                'away_coach' => "ReqCoach A{$n}",
                'judges' => json_encode(["Judge " . Str::upper(Str::random(3))]),
                'location' => "Cēsis, Latvia",
            ]);

            MatchRequest::create([
                'user_id' => $users->random()->id,
                'request_type' => 'create_match',
                'home_team' => "PendingHome {$n}v{$n}",
                'away_team' => "PendingAway {$n}v{$n}",
                'start_time' => now()->addDays($n + 4)->setTime(18, 0),
                'end_time' => now()->addDays($n + 4)->setTime(20, 0),
                'players_per_team' => $n,
                'home_players' => json_encode(array_map(fn($i) => [
                    'first_name' => 'H' . ($i + 1),
                    'last_name'  => 'Pending' . ($i + 1),
                ], range(0, $n - 1))),
                'away_players' => json_encode(array_map(fn($i) => [
                    'first_name' => 'A' . ($i + 1),
                    'last_name'  => 'Pending' . ($i + 1),
                ], range(0, $n - 1))),
                'status' => 'pending',
                'home_coach' => null,
                'away_coach' => null,
                'judges' => json_encode([]),
                'location' => "Rēzekne, Latvia",
            ]);

            MatchRequest::create([
                'user_id' => $users->random()->id,
                'request_type' => 'score_update',
                'home_team' => $adminMatch->home_team_name,
                'away_team' => $adminMatch->away_team_name,
                'start_time' => $adminMatch->start_time,
                'end_time' => $adminMatch->end_time,
                'players_per_team' => $adminMatch->players_per_team,
                'match_id' => $adminMatch->id,
                'score_home' => rand(0, 20),
                'score_away' => rand(0, 20),
                'status' => 'pending',
                'home_coach' => $adminMatch->home_coach,
                'away_coach' => $adminMatch->away_coach,
                'judges' => $adminMatch->judges,
                'location' => $adminMatch->location,
            ]);

            MatchRequest::create([
                'user_id' => $users->random()->id,
                'request_type' => 'score_update',
                'home_team' => $completedMatch->home_team_name,
                'away_team' => $completedMatch->away_team_name,
                'start_time' => $completedMatch->start_time,
                'end_time' => $completedMatch->end_time,
                'players_per_team' => $completedMatch->players_per_team,
                'match_id' => $completedMatch->id,
                'score_home' => $completedMatch->home_score,
                'score_away' => $completedMatch->away_score,
                'status' => 'accepted',
                'home_coach' => $completedMatch->home_coach,
                'away_coach' => $completedMatch->away_coach,
                'judges' => $completedMatch->judges,
                'location' => $completedMatch->location,
            ]);
        }
    }

    /**
     * Generate seat rows for a match (bulk insert).
     */
   private function generateSeats(VolleyballMatch $match): void
{
    // layout parameters
    $rows = 6;   // rows for top/bottom
    $cols = 12;  // columns for top/bottom
    $sideRows = 12;  // vertical rows for side stands
    $sideCols = 4;   // how many columns the side stand uses (you can adjust)
    $now = now();
    $toInsert = [];

    // human labels used by front-end (these will be slugified by the client)
    $stands = [
        ['label' => 'Augšējā tribīne', 'dir' => 'row', 'rows' => $rows, 'cols' => $cols],
        ['label' => 'Apakšējā tribīne', 'dir' => 'row', 'rows' => $rows, 'cols' => $cols],
        ['label' => 'Kreisā tribīne', 'dir' => 'col', 'rows' => $sideRows, 'cols' => $sideCols],
        ['label' => 'Labā tribīne',   'dir' => 'col', 'rows' => $sideRows, 'cols' => $sideCols],
    ];

    // helper to create a slug that matches JS slugify(Str) behaviour (Laravel Str::slug is fine)
    foreach ($stands as $stand) {
        $label = $stand['label'];
        $dir = $stand['dir'];
        $rMax = (int)$stand['rows'];
        $cMax = (int)$stand['cols'];

        // build seat positions per stand:
        if ($dir === 'row') {
            for ($r = 1; $r <= $rMax; $r++) {
                for ($c = 1; $c <= $cMax; $c++) {
                    $slug = \Illuminate\Support\Str::slug($label); // e.g. "augseja-tribine"
                    $seatKey = "{$slug}-{$r}-{$c}";
                    $toInsert[] = [
                        'match_id' => $match->id,
                        'seat_number' => $seatKey,
                        'side' => $label,           // human readable side stored for debugging/optional server use
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
        } else { // 'col' (vertical side stands)
            // We'll keep seatNumber numbering as row/col too, but iterate differently
            for ($c = 1; $c <= $cMax; $c++) {
                for ($r = 1; $r <= $rMax; $r++) {
                    $slug = \Illuminate\Support\Str::slug($label);
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

    // bulk insert in chunks (avoid duplicates with insertOrIgnore)
    foreach (array_chunk($toInsert, 250) as $chunk) {
        DB::table('seats')->insertOrIgnore($chunk);
    }
}


    /**
     * Create some random tickets and assign seats for them.
     */
    private function assignRandomTickets(VolleyballMatch $match, $users): void
    {
        foreach ($users as $user) {
            $numSeats = rand(0, 2); // some users 0-2 seats
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

            $match->home_logo = $homePath;
            $match->away_logo = $awayPath;
            $match->save();
        } catch (\Throwable $e) {
            \Log::warning('Failed to attach logos for match ' . $match->id . ' - ' . $e->getMessage());
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
        return mb_strtoupper($initials ?: substr($name, 0, 3));
    }

    private function generateSimpleSvg(string $text, string $bg): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $bg = htmlspecialchars($bg, ENT_QUOTES, 'UTF-8');
        $svg = <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">
  <rect width="100%" height="100%" rx="16" fill="{$bg}"/>
  <text x="50%" y="50%" font-family="Arial, Helvetica, sans-serif" font-size="120" fill="#ffffff" dominant-baseline="middle" text-anchor="middle">{$text}</text>
</svg>
SVG;
        return $svg;
    }
}
