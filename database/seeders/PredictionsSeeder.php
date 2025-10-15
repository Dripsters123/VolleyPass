<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Prediction;
use App\Models\VolleyballMatch;
use App\Models\Wallet;
use Faker\Factory as Faker;

class PredictionsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Ensure admin exists
        $admin = User::firstWhere('role', 'admin');
        if (!$admin) {
            $admin = User::factory()->create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'role' => 'admin',
                'password' => bcrypt('AdminPassword123!'),
            ]);
        }

        // Give admin a loaded wallet
        $adminWallet = Wallet::firstOrCreate(
            ['user_id' => $admin->id],
            ['coins' => 100000]
        );

        if (Schema::hasTable('wallet_transactions')) {
            DB::table('wallet_transactions')->insert([
                'wallet_id' => $adminWallet->id,
                'user_id' => $admin->id,
                'type' => 'seed',
                'coins' => 100000,
                'status' => 'completed',
                'related_type' => null,
                'related_id' => null,
                'note' => 'Seed: initial admin coins',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Ensure users exist
        $users = User::all();
        if ($users->count() < 4) {
            User::factory()->count(6)->create();
            $users = User::all();
        }

        // Ensure matches exist
        $matches = VolleyballMatch::all();
        if ($matches->isEmpty()) {
            \Log::warning('PredictionsSeeder: no VolleyballMatch entries found. Run LocalMatchSeeder first.');
            return;
        }

        foreach ($matches as $match) {
            $sample = $users->random(min(max(3, rand(3, 8)), $users->count()));

            $homeScore = intval($match->home_score ?? 0);
            $awayScore = intval($match->away_score ?? 0);
            $actualOutcome = ($match->match_state ?? $match->status_type ?? '') === 'completed'
                ? ($homeScore > $awayScore ? 'home' : 'away')
                : null;

            foreach ($sample as $user) {
                if (Prediction::where('user_id', $user->id)->where('match_id', $match->id)->exists()) {
                    continue;
                }

                $choice = $user->id === $admin->id ? 'home' : $this->randomPredictionChoice();
                $status = $actualOutcome
                    ? ($choice === $actualOutcome ? 'won' : 'lost')
                    : 'pending';

                $rewardCoins = 0;
                if ($status === 'won') {
                    $rewardCoins = rand(100, 500); // 100–500 coins per win
                    $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['coins' => 0]);
                    $wallet->increment('coins', $rewardCoins);

                    if (Schema::hasTable('wallet_transactions')) {
                        DB::table('wallet_transactions')->insert([
                            'wallet_id' => $wallet->id,
                            'user_id' => $user->id,
                            'type' => 'prediction_win',
                            'coins' => $rewardCoins,
                            'status' => 'completed',
                            'related_type' => Prediction::class,
                            'related_id' => null,
                            'note' => "Seed: prediction reward for match {$match->id}",
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                Prediction::create([
                    'user_id' => $user->id,
                    'match_id' => $match->id,
                    'prediction' => $choice,
                    'staked_coins' => rand(0, 50),
                    'status' => $status,
                    'reward' => $rewardCoins,
                ]);
            }
        }
    }

    private function randomPredictionChoice(): string
    {
        return rand(0, 1) === 0 ? 'home' : 'away';
    }
}
