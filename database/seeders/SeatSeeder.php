<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Seat;
use App\Models\VolleyballMatch;

class SeatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all upcoming matches
        $upcomingMatches = VolleyballMatch::where('status_type', 'upcoming')->get();

        if ($upcomingMatches->isEmpty()) {
            $this->command->warn('No upcoming matches found. Run SportDevs sync first.');
            return;
        }

        foreach ($upcomingMatches as $match) {
            // Example configuration: 6x12 top/bottom, 6x12 side stands
            $rows = 6;
            $cols = 12;
            $sideCols = 6;
            $sideRows = 12;

            // Top stand
            for ($r = 1; $r <= $rows; $r++) {
                for ($c = 1; $c <= $cols; $c++) {
                    Seat::create([
                        'match_id' => $match->id,
                        'seat_number' => "Top-{$r}-{$c}",
                        'is_taken' => rand(0, 10) < 2, // ~20% seats taken
                        'is_fake' => true,
                    ]);
                }
            }

            // Bottom stand
            for ($r = 1; $r <= $rows; $r++) {
                for ($c = 1; $c <= $cols; $c++) {
                    Seat::create([
                        'match_id' => $match->id,
                        'seat_number' => "Bottom-{$r}-{$c}",
                        'is_taken' => rand(0, 10) < 2,
                        'is_fake' => true,
                    ]);
                }
            }

            // Left stand
            for ($c = 1; $c <= $sideCols; $c++) {
                for ($r = 1; $r <= $sideRows; $r++) {
                    Seat::create([
                        'match_id' => $match->id,
                        'seat_number' => "Left-{$r}-{$c}",
                        'is_taken' => rand(0, 10) < 2,
                        'is_fake' => true,
                    ]);
                }
            }

            // Right stand
            for ($c = 1; $c <= $sideCols; $c++) {
                for ($r = 1; $r <= $sideRows; $r++) {
                    Seat::create([
                        'match_id' => $match->id,
                        'seat_number' => "Right-{$r}-{$c}",
                        'is_taken' => rand(0, 10) < 2,
                        'is_fake' => true,
                    ]);
                }
            }

            $this->command->info("Seats seeded for match ID: {$match->id}");
        }
    }
}
