<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use Illuminate\Http\Request;

class SeatController extends Controller
{
    public function index($matchId)
    {
        return Seat::where('match_id', $matchId)->get();
    }

    public function reserve(Request $request, $seatId)
    {
        $seat = Seat::lockForUpdate()->findOrFail($seatId);

        if ($seat->is_taken) {
            return response()->json(['error' => 'Seat already taken'], 409);
        }

        // Temporary hold before Stripe payment (optional)
        $seat->update(['is_taken' => true, 'user_id' => auth()->id()]);

        return response()->json($seat);
    }
    public function show($matchId)
{
    $seats = Seat::where('match_id', $matchId)->get();

    // List of seat IDs that are taken
    $takenSeats = $seats->where('is_taken', true)
                        ->map(fn($s) => $s->seat_number)
                        ->toArray();

    return view('matches.seats', [
        'matchId' => $matchId,
        'takenSeats' => $takenSeats,
        'rows' => 6,
        'cols' => 12,
        'sideRows' => 12,
        'sideCols' => 6
    ]);
}

}
