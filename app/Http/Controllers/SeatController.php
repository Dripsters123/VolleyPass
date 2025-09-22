<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\VolleyballMatch;
use Illuminate\Http\Request;

class SeatController extends Controller
{
    public function index($matchId)
    {
        return Seat::where('match_id', $matchId)->get();
    }

    public function reserve(Request $request, $seatNumber)
    {
        // Find seat by seat_number instead of numeric ID
        $seat = Seat::lockForUpdate()->where('seat_number', $seatNumber)->firstOrFail();

        if ($seat->is_taken) {
            return response()->json(['error' => 'Seat already taken'], 409);
        }

        // Reserve seat for current user
        $seat->update(['is_taken' => true, 'user_id' => auth()->id()]);

        return response()->json($seat);
    }

    public function show($matchId)
    {
        $match = VolleyballMatch::with('arena')->findOrFail($matchId);

        $seats = Seat::where('match_id', $matchId)->get();

        // Directly use seat_number for JS
        $takenSeats = $seats->filter(fn($s) => $s->is_taken)
                            ->pluck('seat_number')
                            ->toArray();

        $seatPrices = [];
        foreach ($seats as $seat) {
            $seatPrices[$seat->seat_number] = $seat->price ?? $match->ticket_price ?? 10;
        }

        return view('matches.show', compact('match', 'takenSeats', 'seatPrices'));
    }
}
