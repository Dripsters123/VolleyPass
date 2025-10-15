<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Wallet;
use App\Models\DiscountCard;

class WalletController extends Controller
{
  
    public function show(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['coins' => 0]
        );

        $cards = DiscountCard::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $costMap = [
            10 => 1000,
            15 => 1500,
            20 => 2000,
            25 => 2500,
        ];

        return view('wallet.buy_discount', compact('wallet', 'cards', 'costMap'));
    }

    /**
     * POST: Buy a discount card using coins
     */
    public function buyDiscountCard(Request $request)
    {
        $request->validate([
            'discount_percent' => 'required|in:10,15,20,25',
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $costMap = [
            10 => 1000,
            15 => 1500,
            20 => 2000,
            25 => 2500,
        ];

        $percent = (int) $request->input('discount_percent');
        $cost = $costMap[$percent] ?? null;

        if ($cost === null) {
            return response()->json(['error' => 'Invalid discount_percent'], 400);
        }

        DB::beginTransaction();
        try {
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

            if (! $wallet) {
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'coins' => 0,
                ]);
            }

            if ($wallet->coins < $cost) {
                DB::rollBack();
                return response()->json(['error' => 'Insufficient coins'], 400);
            }

            $wallet->debitCoins($cost, 'buy_discount', [
                'note' => "Bought {$percent}% discount card",
            ]);

            $code = DiscountCard::generateUniqueCode('DC', 8);

            $card = DiscountCard::create([
                'user_id' => $user->id,
                'code' => $code,
                'discount_percent' => $percent,
                'active' => true,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'discount_card' => $card,
                'message' => "Bought {$percent}% discount card for {$cost} coins.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Buy discount failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Could not complete purchase'], 500);
        }
    }

    public function listDiscountCards(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $cards = DiscountCard::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['cards' => $cards]);
    }
    
}
