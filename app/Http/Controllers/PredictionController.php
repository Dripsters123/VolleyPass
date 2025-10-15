<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Prediction;
use App\Models\VolleyballMatch;
use App\Models\Wallet;

class PredictionController extends Controller
{
    /**
     * Show predictions UI (upcoming + completed) + a small "Manas prognozes" box.
     * Upcoming matches are paginated to handle large numbers.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();

        // Upcoming (future) matches - paginate so many matches don't kill the page.
        $upcoming = VolleyballMatch::where('is_local', true)
            ->where('start_time', '>', now())
            ->orderBy('start_time', 'asc')
            ->paginate(8)
            ->withQueryString();

        // Completed matches (limit to recent N for sidebar; keep it scrollable)
        $completed = VolleyballMatch::where('is_local', true)
            ->where('start_time', '<=', now())
            ->orderByDesc('start_time')
            ->limit(100)
            ->get();

        // User data
        $userPreds = Prediction::where('user_id', $userId)
            ->pluck('prediction', 'match_id')
            ->toArray();

        $stakes = Prediction::where('user_id', $userId)
            ->pluck('staked_coins', 'match_id')
            ->toArray();

        $rewards = Prediction::where('user_id', $userId)
            ->pluck('reward', 'match_id')
            ->toArray();

        // recent predictions for sidebar preview
        $recentPredictions = Prediction::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        $recentMatchIds = $recentPredictions->pluck('match_id')->unique();
        $recentMatches = VolleyballMatch::whereIn('id', $recentMatchIds)->get()->keyBy('id');

        return view('predictions.index', compact(
            'upcoming',
            'completed',
            'userPreds',
            'stakes',
            'rewards',
            'recentPredictions',
            'recentMatches'
        ));
    }

    /**
     * Store or update a prediction.
     * Enforces:
     *  - only one prediction per user per match (create or update)
     *  - can edit only if match hasn't started (no betting on ongoing)
     *  - properly debit/credit wallet on first stake and when stake changes
     *  - returns friendly Latvian error messages (no 500 from wallet exceptions)
     */
    public function store(Request $request)
    {
        $v = $request->validate([
            'match_id'     => 'required|exists:volleyball_matches,id',
            'prediction'   => 'required|in:home,away',
            'staked_coins' => 'nullable|numeric|min:0'
        ]);

        $user = $request->user();
        $match = VolleyballMatch::findOrFail($v['match_id']);

        // If match has started or is ongoing, do not allow create/update
        if (now()->gte($match->start_time)) {
            return back()->with('error', 'Nevar veidot vai rediģēt prognozes, ja mačs ir sācies.');
        }

        $newStake = round(floatval($v['staked_coins'] ?? 0), 2);
        $newPick = $v['prediction'];

        // Find existing prediction if any
        $prediction = Prediction::where('user_id', $user->id)
            ->where('match_id', $match->id)
            ->first();

        // Ensure wallet exists
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);

        // Helper closures: handle debit/credit and catch exceptions from Wallet model
        $debit = function($amount, $note = null) use ($wallet, $user) {
            if ($amount <= 0) return true;

            try {
                if (method_exists($wallet, 'debit')) {
                    // Wallet::debit may throw when insufficient; catch below
                    $res = $wallet->debit($amount, 'prediction_stake', $user->id, null, $note);
                    // Some implementations return truthy/falsey
                    return $res !== false;
                }

                // fallback manual debit
                if ($wallet->balance < $amount) {
                    return false;
                }
                $wallet->balance -= $amount;
                $wallet->save();
                if (Schema::hasTable('wallet_transactions')) {
                    DB::table('wallet_transactions')->insert([
                        'wallet_id' => $wallet->id,
                        'user_id' => $user->id,
                        'type' => 'prediction_stake',
                        'amount' => -1 * $amount,
                        'status' => 'completed',
                        'related_type' => null,
                        'related_id' => null,
                        'note' => $note ?? 'Stake for prediction',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                return true;
            } catch (\Throwable $e) {
                // Do NOT bubble — return false so controller can show user-friendly message
                return false;
            }
        };

        $credit = function($amount, $note = null) use ($wallet, $user) {
            if ($amount <= 0) return true;
            try {
                if (method_exists($wallet, 'credit')) {
                    return $wallet->credit($amount, 'prediction_refund', $user->id, null, $note);
                }
                $wallet->balance += $amount;
                $wallet->save();
                if (Schema::hasTable('wallet_transactions')) {
                    DB::table('wallet_transactions')->insert([
                        'wallet_id' => $wallet->id,
                        'user_id' => $user->id,
                        'type' => 'prediction_refund',
                        'amount' => $amount,
                        'status' => 'completed',
                        'related_type' => null,
                        'related_id' => null,
                        'note' => $note ?? 'Refund for prediction',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                return true;
            } catch (\Throwable $e) {
                return false;
            }
        };

        // CASE: update existing prediction
        if ($prediction) {
            $oldStake = round(floatval($prediction->staked_coins ?? 0), 2);
            $oldPick = $prediction->prediction;

            // nothing changed
            if ($oldPick === $newPick && abs($oldStake - $newStake) < 0.0001) {
                return back()->with('success', 'Izmaiņas netika konstatētas.');
            }

            // If stake increased, attempt to debit the difference
            if ($newStake > $oldStake) {
                $diff = round($newStake - $oldStake, 2);
                if (!$debit($diff, "Palielināta likme uz maču {$match->id}")) {
                    return back()->with('error', 'Nepietiek līdzekļu, lai palielinātu likmi.');
                }
            }

            // If stake decreased, refund the difference
            if ($newStake < $oldStake) {
                $diff = round($oldStake - $newStake, 2);
                $credit($diff, "Atmaksa par likmes samazināšanu uz maču {$match->id}");
            }

            // update prediction
            $prediction->prediction = $newPick;
            $prediction->staked_coins = $newStake;
            $prediction->status = 'pending';
            $prediction->save();

            return back()->with('success', 'Prognoze atjaunināta.');
        }

        // CASE: create new prediction
        if ($newStake > 0) {
            if (!$debit($newStake, "Likme par prognozi uz maču {$match->id}")) {
                return back()->with('error', 'Nepietiek līdzekļu, lai veiktu likmi.');
            }
        }

        Prediction::create([
            'user_id' => $user->id,
            'match_id' => $match->id,
            'prediction' => $newPick,
            'staked_coins' => $newStake,
            'status' => 'pending',
            'reward' => null,
        ]);

        return back()->with('success', 'Prognoze veikta.');
    }

    /**
     * View with all user's predictions ("Manas prognozes")
     */
    public function myPredictions()
    {
        $userId = auth()->id();

        $predictions = Prediction::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate(20);

        $matchIds = $predictions->pluck('match_id')->unique();
        $matches = VolleyballMatch::whereIn('id', $matchIds)->get()->keyBy('id');

        return view('predictions.my_predictions', compact('predictions', 'matches'));
    }

    public function show(Prediction $prediction)
    {
        $this->authorize('view', $prediction);
        return view('predictions.show', compact('prediction'));
    }
}
