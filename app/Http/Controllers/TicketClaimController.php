<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ticket;
use App\Models\Wallet;
use App\Models\WalletTransaction;

class TicketClaimController extends Controller
{
    /**
     * Claim coins for a ticket.
     */
    public function claim(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        // 🧱 Ownership check
        if ($ticket->user_id !== $user->id) {
            return back()->with('error', 'Tu nevari pieprasīt monētas par šo biļeti.');
        }

        // Already claimed?
        if ($ticket->coin_reward_claimed) {
            return back()->with('error', 'Monētas jau pieprasītas.');
        }

        DB::beginTransaction();
        try {
            // 🔒 Lock ticket to prevent concurrent claims
            $ticket = Ticket::where('id', $ticket->id)->lockForUpdate()->first();

            if ($ticket->coin_reward_claimed) {
                DB::rollBack();
                return back()->with('error', 'Monētas jau pieprasītas.');
            }

            // 🧮 Calculate reward based on actual seats
            $seatCount = $ticket->seats()->count() ?: $ticket->quantity;
            $reward = $seatCount * 50;

            // 🏦 Get or create wallet
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['coins' => 0]
            );

            // 💰 Add coins
            $wallet->coins += $reward;
            $wallet->save();

            // 🧾 Record transaction
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'type' => 'ticket_reward',
                'coins' => $reward,
                'status' => 'completed',
                'related_type' => Ticket::class,
                'related_id' => $ticket->id,
                'note' => "Reward for Ticket #{$ticket->id}",
            ]);

            // ✅ Mark ticket as claimed
            $ticket->update([
                'coin_reward_claimed' => true,
                'claimed_at' => now(),
            ]);

            DB::commit();
            return back()->with('success', "Tu saņēmi {$reward} monētas!");
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error("Ticket coin claim failed: " . $e->getMessage());
            return back()->with('error', 'Neizdevās pieprasīt monētas.');
        }
    }

    /**
     * Optional admin override to mark a ticket as claimed.
     */
    public function adminClaim(Request $request, Ticket $ticket)
    {
        DB::transaction(function () use ($ticket) {
            $ticket->update(['coin_reward_claimed' => true, 'claimed_at' => now()]);
        });

        return back()->with('success', 'Admin: monētas atzīmētas kā pieprasītas.');
    }
}
