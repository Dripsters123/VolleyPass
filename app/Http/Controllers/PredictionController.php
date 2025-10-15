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
 
    public function index(Request $request)
    {
        $userId = auth()->id();

       
        $upcoming = VolleyballMatch::where('is_local', true)
            ->where('start_time', '>', now())
            ->orderBy('start_time', 'asc')
            ->paginate(8)
            ->withQueryString();

      
        $completed = VolleyballMatch::where('is_local', true)
            ->where('start_time', '<=', now())
            ->orderByDesc('start_time')
            ->limit(100)
            ->get();

       
        $userPreds = Prediction::where('user_id', $userId)
            ->pluck('prediction', 'match_id')
            ->toArray();

        $stakes = Prediction::where('user_id', $userId)
            ->pluck('staked_coins', 'match_id')
            ->toArray();

        $rewards = Prediction::where('user_id', $userId)
            ->pluck('reward', 'match_id')
            ->toArray();

       
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


   public function store(Request $request)
{
    $v = $request->validate([
        'match_id'     => 'required|exists:volleyball_matches,id',
        'prediction'   => 'required|in:home,away',
        'staked_coins' => 'nullable|numeric|min:0'
    ]);

    $user = $request->user();
    $match = VolleyballMatch::findOrFail($v['match_id']);

    if (now()->gte($match->start_time)) {
        return back()->with('error', 'Nevar veidot vai rediģēt prognozes, ja mačs ir sācies.');
    }

    $newStake = round(floatval($v['staked_coins'] ?? 0), 2);

    $toCoins = function($decimal) {
        
        return (int) round($decimal);
    };
    $newStakeCoins = $toCoins($newStake);
    $newPick = $v['prediction'];

    $prediction = Prediction::where('user_id', $user->id)
        ->where('match_id', $match->id)
        ->first();

    $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['coins' => 0]);

    $debit = function(int $coins, $note = null) use ($wallet, $user) {
        if ($coins <= 0) return true;
        try {
            if (method_exists($wallet, 'debit')) {
                $res = $wallet->debit($coins, 'prediction_stake', $user->id, null, $note);
                return $res !== false;
            }
            if ($wallet->coins < $coins) {
                return false;
            }
            $wallet->coins -= $coins;
            $wallet->save();
            if (Schema::hasTable('wallet_transactions')) {
                DB::table('wallet_transactions')->insert([
                    'wallet_id'   => $wallet->id,
                    'user_id'     => $user->id,
                    'type'        => 'prediction_stake',
                    'coins'       => -1 * $coins,
                    'status'      => 'completed',
                    'related_type'=> null,
                    'related_id'  => null,
                    'note'        => $note ?? 'Stake for prediction',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    };

    $credit = function(int $coins, $note = null) use ($wallet, $user) {
        if ($coins <= 0) return true;
        try {
            if (method_exists($wallet, 'credit')) {
                $res = $wallet->credit($coins, 'prediction_refund', $user->id, null, $note);
                return $res !== false;
            }
            $wallet->coins += $coins;
            $wallet->save();
            if (Schema::hasTable('wallet_transactions')) {
                DB::table('wallet_transactions')->insert([
                    'wallet_id'   => $wallet->id,
                    'user_id'     => $user->id,
                    'type'        => 'prediction_refund',
                    'coins'       => $coins,
                    'status'      => 'completed',
                    'related_type'=> null,
                    'related_id'  => null,
                    'note'        => $note ?? 'Refund for prediction',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    };

    if ($prediction) {
        $oldStake = round(floatval($prediction->staked_coins ?? 0), 2);
        $oldStakeCoins = $toCoins($oldStake);
        $oldPick = $prediction->prediction;

        if ($oldPick === $newPick && $oldStakeCoins === $newStakeCoins) {
            return back()->with('success', 'Izmaiņas netika konstatētas.');
        }

        if ($newStakeCoins > $oldStakeCoins) {
            $diff = $newStakeCoins - $oldStakeCoins;
            if (!$debit($diff, "Palielināta likme uz maču {$match->id}")) {
                return back()->with('error', 'Nepietiek līdzekļu, lai palielinātu likmi.');
            }
        }

        if ($newStakeCoins < $oldStakeCoins) {
            $diff = $oldStakeCoins - $newStakeCoins;
            $credit($diff, "Atmaksa par likmes samazināšanu uz maču {$match->id}");
        }

        $prediction->prediction = $newPick;
        $prediction->staked_coins = $newStake;
        $prediction->status = 'pending';
        $prediction->save();

        return back()->with('success', 'Prognoze atjaunināta.');
    }

    if ($newStakeCoins > 0) {
        if (!$debit($newStakeCoins, "Likme par prognozi uz maču {$match->id}")) {
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
