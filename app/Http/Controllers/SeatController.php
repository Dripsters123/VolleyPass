<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\VolleyballMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SeatController extends Controller
{
    public function index($matchId)
    {
        return Seat::where('match_id', $matchId)->get();
    }

     public function reserve(Request $request, $seatId)
    {
        if (! $request->user()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $user = $request->user();

        try {
            return DB::transaction(function () use ($seatId, $user) {
                $seat = Seat::where('id', $seatId)->lockForUpdate()->firstOrFail();

                if ($seat->ticket_id !== null) {
                    return response()->json(['error' => 'Seat already taken'], 409);
                }

                if ($seat->reserved_by !== null && $seat->reserved_by !== $user->id) {
                    if ($seat->reserved_until === null || $seat->reserved_until->isFuture()) {
                        return response()->json(['error' => 'Seat temporarily reserved'], 409);
                    }
                }

                $seat->reserved_by = $user->id;
                $seat->reserved_until = Carbon::now()->addMinutes(15);
                $seat->save();

                return response()->json([
                    'status' => 'reserved',
                    'seat_id' => $seat->id,
                    'reserved_until' => $seat->reserved_until->toDateTimeString(),
                ]);
            });
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Seat not found'], 404);
        } catch (\Throwable $e) {
            \Log::error('Seat reserve error', ['seatId' => $seatId, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Server error while reserving seat'], 500);
        }
    }


    public function show($matchId)
    {
        $match = VolleyballMatch::findOrFail($matchId);
        $seats = Seat::where('match_id', $matchId)->get();

        $sideLabel = function ($sideSlug) {
            if (is_null($sideSlug)) return 'Augšējā tribīne';
            $s = mb_strtolower(trim((string)$sideSlug));
            if (str_contains($s, 'top') || str_contains($s, 'aug')) return 'Augšējā tribīne';
            if (str_contains($s, 'left') || str_contains($s, 'kreis')) return 'Kreisā tribīne';
            if (str_contains($s, 'right') || str_contains($s, 'lab')) return 'Labā tribīne';
            if (str_contains($s, 'bot') || str_contains($s, 'apak')) return 'Apakšējā tribīne';
            return mb_convert_case($sideSlug, MB_CASE_TITLE, "UTF-8");
        };

        $takenSeats = [];
        $takenSeatIds = [];
        $seatPrices = [];
        $seatIdMap = [];

        foreach ($seats as $s) {
            $side = $s->side ?? null;
            $row = $s->row ?? null;
            $number = $s->number ?? null;

            if (($row === null || $number === null) && !empty($s->seat_number)) {
    $raw = trim($s->seat_number);
    if (preg_match('/(?:^\d+-)?([^\-]+)-(\d+)-(\d+)$/u', $raw, $m)) {
        $side = $side ?? $m[1];
        $row = $row ?? intval($m[2]);
        $number = $number ?? intval($m[3]);
    }
}

            if ($row === null || $number === null) continue;

            $label = $sideLabel($side ?? null);
            $humanKey = "{$label}-{$row}-{$number}";
            $slugKey = Str::slug($label, '-') . "-{$row}-{$number}";

            if (!is_null($s->ticket_id)) {
                $takenSeats[] = $humanKey;
                $takenSeatIds[] = $s->id;
            }

            $seatPrices[$humanKey] = $s->price;
            $seatIdMap[$humanKey] = $s->id;
            $seatIdMap[$s->seat_number] = $s->id;        
            if (!isset($seatIdMap[$slugKey])) {
            $seatPrices[$slugKey] = $s->price;
            $seatIdMap[$slugKey] = $s->id;
            }
        }

        return view('matches.local_matches', compact('match', 'takenSeats', 'takenSeatIds', 'seatPrices', 'seatIdMap'));
    }
}
