<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Seat;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class VolleyballMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_local',
        'home_team_name',
        'away_team_name',
        'players_per_team',
        'status_type',
        'start_time',
        'end_time',
        'home_score',
        'away_score',
        'home_players',
        'away_players',
        'ticket_price',
        'home_coach',
        'away_coach',
        'location',
        'judges',
        'home_logo',
        'away_logo',
        'home_color',
        'away_color',
        'estimated_duration_minutes',
        'actual_end_time',
        'match_state',
    ];

    protected $casts = [
        'home_players' => 'array',
        'away_players' => 'array',
        'judges' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'actual_end_time' => 'datetime',
    ];
    public function sets()
{
    return $this->hasMany(VolleyballMatchSet::class, 'match_id');
}

    public function media()
    {
        return $this->hasMany(MatchMedia::class, 'match_id');
    }
      public function verifications()
    {
        return $this->hasMany(MatchScoreVerification::class, 'match_id');
    }
    public function seats()
    {
        return $this->hasMany(Seat::class, 'match_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'event_id');
    }

   public function generateSeats(array $sides = null, int $rows = null, int $cols = null, ?float $price = null): int
{
    if (! $this->is_local) {
        return 0;
    }

    $this->seats()->delete();

    $arenaCfg = is_array($this->arena) ? $this->arena : [];
    $sides = $sides ?? ($arenaCfg['sides'] ?? ['top', 'bottom', 'left', 'right']);
    $rows  = $rows  ?? ($arenaCfg['rows']  ?? 6);
    $cols  = $cols  ?? ($arenaCfg['cols']  ?? 12);
    $price = $price ?? ($this->ticket_price ?? 10.0);

    $created = 0;

    foreach ($sides as $side) {
        for ($r = 1; $r <= $rows; $r++) {
            for ($c = 1; $c <= $cols; $c++) {
                Seat::create([
                    'match_id'       => $this->id,
                    'side'           => $side,
                    'row'            => $r,
                    'number'         => $c,
                    'seat_number'    => "{$side}-{$r}-{$c}",
                    'price'          => $price,
                    'ticket_id'      => null,
                    'user_id'        => null,
                    'reserved_by'    => null,
                    'reserved_until' => null,
                ]);
                $created++;
            }
        }
    }

    return $created;
}


    protected static function booted()
    {
        static::saved(function (self $match) {
            if ($match->is_local) {
               
                try {
                   Event::updateOrCreate(
                        ['id' => $match->id],
                        [
                            'name' => ($match->home_team_name ?? 'Match') . ' vs ' . ($match->away_team_name ?? ''),
                            'description' => $match->tournament ?? ($match->tournament_name ?? null),
                            'start_time' => $match->start_time,
                            'end_time' => $match->end_time,
                        ]
                    );
                } catch (\Throwable $e) {
                    Log::warning("Event sync failed for match {$match->id}: " . $e->getMessage());
                }

                try {
                    $existingCount = DB::table('seats')->where('match_id', $match->id)->count();
                } catch (\Throwable $e) {
                    Log::warning("Could not query seats for match {$match->id}: " . $e->getMessage());
                    $existingCount = 0;
                }

                if ($existingCount === 0) {
                    try {
                        $sides = ['left', 'right', 'top', 'bottom'];
                        $rowsPerSide = 6;
                        $seatsPerRow = 12;
                        $seatPrice = $match->ticket_price ?? 10.00;

                        $toInsert = [];
                        for ($sideIndex = 0; $sideIndex < count($sides); $sideIndex++) {
                            $side = $sides[$sideIndex];
                            for ($row = 1; $row <= $rowsPerSide; $row++) {
                                for ($number = 1; $number <= $seatsPerRow; $number++) {
                                    $seatNumber = "{$side}-{$row}-{$number}";
                                    $toInsert[] = [
                                        'match_id'    => $match->id,
                                        'seat_number' => $seatNumber,
                                        'side'        => $side,
                                        'row'         => $row,
                                        'number'      => $number,
                                        'price'       => $seatPrice,
                                        'ticket_id'   => null,
                                        'user_id'     => null,
                                        'created_at'  => now(),
                                        'updated_at'  => now(),
                                    ];
                                }
                            }
                        }

                        $chunks = array_chunk($toInsert, 250);
                        $totalInserted = 0;
                        foreach ($chunks as $chunk) {
                            $res = DB::table('seats')->insertOrIgnore($chunk);
                            if (is_int($res)) {
                                $totalInserted += $res;
                            } else {
                                foreach ($chunk as $row) {
                                    if (DB::table('seats')->where('match_id', $row['match_id'])->where('seat_number', $row['seat_number'])->exists()) {
                                        $totalInserted++;
                                    }
                                }
                            }
                        }

                        Log::info("✅ Seats generation attempted for local match ID {$match->id}. Inserted: {$totalInserted}");
                    } catch (\Throwable $e) {
                        Log::error("❌ Seat generation failed for match {$match->id}: " . $e->getMessage());
                    }
                } else {
                    Log::info("Seats already exist for match ID {$match->id} — skipping generation ({$existingCount} rows)");
                }
            }
        });
    }
}
