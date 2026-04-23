<?php

namespace App\Http\Controllers;

use App\Models\Arena;
use App\Models\Ticket;
use App\Models\VolleyballMatch;
use App\Models\MatchRequest;
use App\Models\MatchScoreVerification;
use App\Models\MatchMedia;
use App\Models\VolleyballMatchSet;
use App\Models\Prediction;
use App\Models\User;
use App\Notifications\MatchFinalized;
use App\Notifications\ScoreUpdateRequested;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MatchController extends Controller
{
  
public function index(Request $request)
{
    $perPage = 12;
    $tab = $request->input('tab', 'upcoming'); // upcoming | results_pending | completed

    $query = VolleyballMatch::query()->where('is_local', true);

    if ($request->filled('players_per_team')) {
        $query->where('players_per_team', $request->players_per_team);
    }

    if ($request->filled('team_name')) {
        $term = $request->team_name;
        $query->where(function ($q) use ($term) {
            $q->where('home_team_name', 'like', "%{$term}%")
              ->orWhere('away_team_name', 'like', "%{$term}%");
        });
    }

    if ($request->filled('location')) {
        $term = $request->location;
        $query->where('location', 'like', "%{$term}%");
    }

    if ($request->filled('date_from')) {
        $query->whereDate('start_time', '>=', $request->date_from);
    }
    if ($request->filled('date_to')) {
        $query->whereDate('start_time', '<=', $request->date_to);
    }

    if ($request->filled('max_price')) {
        $query->where('ticket_price', '<=', (float)$request->max_price);
    }

    // Apply tab filter at DB level for efficiency
    if ($tab === 'completed') {
        $query->where(function ($q) {
            $q->where('match_state', 'completed')->orWhere('status_type', 'completed');
        });
        $query->orderBy('start_time', 'desc');
    } elseif ($tab === 'results_pending') {
        $query->where(function ($q) {
            $q->where('match_state', '!=', 'completed')->orWhereNull('match_state');
        })->where(function ($q) {
            $q->where('status_type', '!=', 'completed')->orWhereNull('status_type');
        })->where('end_time', '<', now());
        $query->orderBy('end_time', 'desc');
    } else {
        // upcoming: end_time in future or not yet ended
        $query->where(function ($q) {
            $q->where('match_state', '!=', 'completed')->orWhereNull('match_state');
        })->where(function ($q) {
            $q->where('status_type', '!=', 'completed')->orWhereNull('status_type');
        })->where(function ($q) {
            $q->whereNull('end_time')->orWhere('end_time', '>=', now());
        });
        $query->orderBy('start_time', 'asc');
    }

    $matches = $query->paginate($perPage)->withQueryString();

    return view('matches.local.index', compact('matches', 'tab'));
}

    public function create(Request $request)
    {
        $prefill = null;
        $userId = auth()->id();

        $myArenas = Arena::where('user_id', $userId)->orderBy('name')->get();

        $favorites = Arena::whereHas('favoritedBy', fn($q) => $q->where('user_id', $userId))
            ->where('user_id', '!=', $userId)
            ->orderBy('name')
            ->get();

        $defaultLayouts = Arena::where('is_public', true)
            ->whereHas('user', fn($q) => $q->where('role', 'admin'))
            ->orderBy('name')
            ->get();

        if ($request->filled('request_id')) {
            $mr = MatchRequest::find($request->request_id);
            if ($mr) {
                $prefill = [
                    'home_team_name' => $mr->home_team,
                    'away_team_name' => $mr->away_team,
                    'start_time'     => $mr->start_time?->format('Y-m-d\TH:i') ?? null,
                    'end_time'       => $mr->end_time?->format('Y-m-d\TH:i') ?? null,
                    'players_per_team' => $mr->players_per_team,
                    'home_players'   => is_array($mr->home_players) ? $mr->home_players : (json_decode($mr->home_players ?? '[]', true) ?: []),
                    'away_players'   => is_array($mr->away_players) ? $mr->away_players : (json_decode($mr->away_players ?? '[]', true) ?: []),
                    'home_coach'     => $mr->home_coach ?? null,
                    'away_coach'     => $mr->away_coach ?? null,
                    'judges'         => is_string($mr->judges) ? json_decode($mr->judges, true) : ($mr->judges ?? []),
                    'location'       => $mr->location ?? null,
                    'home_logo'      => $mr->home_logo ?? null,
                    'away_logo'      => $mr->away_logo ?? null,
                    'match_request_id' => $mr->id,
                    'arena_name'     => $mr->arena_name ?? ($mr->home_team . ' vs ' . $mr->away_team . ' Arena'),
                    'arena_layout'   => json_decode($mr->arena_layout, true) ?? [],
                    'arena_elements' => json_decode($mr->arena_elements, true) ?? [],
                    'arena_width'    => $mr->arena_width ?? 800,
                    'arena_height'   => $mr->arena_height ?? 600,
                    'ticket_price'   => $mr->ticket_price ?? null,
                ];
            }
        }

        // also provide a combined collection for legacy views that expect $arenas
        $arenas = $myArenas->merge($favorites)->merge($defaultLayouts)->unique('id')->values();

        return view('admin.matches.create', compact('prefill', 'myArenas', 'favorites', 'defaultLayouts', 'arenas'));
    }

    protected function hasTimeOverlap($startIso, $endIso, $excludeMatchId = null): bool
    {
        $start = Carbon::parse($startIso);
        $end = Carbon::parse($endIso);

        $query = VolleyballMatch::where('is_local', true);

        if ($excludeMatchId) {
            $query->where('id', '!=', $excludeMatchId);
        }

        return $query->where(function ($q) use ($start, $end) {
            $q->where('start_time', '<', $end)
              ->where('end_time', '>', $start);
        })->exists();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'home_team_name' => 'required|string|max:255',
            'away_team_name' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
            'players_per_team' => 'required|integer|in:2,4,6',
            'ticket_price' => 'nullable|numeric|min:0',
            'home_coach' => 'nullable|string|max:255',
            'away_coach' => 'nullable|string|max:255',
            'judges' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
            'home_logo' => 'nullable|file|image|mimes:jpg,jpeg,png,svg|max:4096',
            'away_logo' => 'nullable|file|image|mimes:jpg,jpeg,png,svg|max:4096',
            'arena_name' => 'required|string|max:255',
            'arena_layout' => 'nullable',
            'arena_elements' => 'nullable',
            'arena_width' => 'nullable|integer|min:400|max:2000',
            'arena_height' => 'nullable|integer|min:300|max:1500',
            'match_request_id' => 'nullable|exists:match_requests,id',
            'home_players' => 'required|array|min:1',
            'home_players.*.first_name' => 'required|string|max:100',
            'home_players.*.last_name' => 'required|string|max:100',
            'away_players' => 'required|array|min:1',
            'away_players.*.first_name' => 'required|string|max:100',
            'away_players.*.last_name' => 'required|string|max:100',
        ]);

        $start = Carbon::parse($validated['start_time']);
        $now = Carbon::now();
        if ($start->lessThan($now->addMinutes(5))) {
            return back()->withInput()->withErrors(['start_time' => 'Sākuma laiks jābūt vismaz 5 minūtes nākotnē.']);
        }

        $end = isset($validated['end_time']) ? Carbon::parse($validated['end_time']) : $start->copy()->addHours(2);

        if ($this->hasTimeOverlap($start->toDateTimeString(), $end->toDateTimeString())) {
            return back()->withInput()->withErrors(['start_time' => 'Šajā laikā jau ir plānots cits mačs.']);
        }

        $judgesArr = [];
        if (!empty($validated['judges'])) {
            $parts = array_map('trim', explode(',', $validated['judges']));
            $judgesArr = array_values(array_filter($parts, fn($x) => $x !== ''));
        }

        $arenaLayout = $validated['arena_layout'] ?? [];
        if (is_string($arenaLayout)) {
            $arenaLayout = json_decode($arenaLayout, true) ?: [];
        }
        $arenaElements = $validated['arena_elements'] ?? [];
        if (is_string($arenaElements)) {
            $arenaElements = json_decode($arenaElements, true) ?: [];
        }

        // Create arena for the match
        $arena = \App\Models\Arena::create([
            'name' => $validated['arena_name'],
            'user_id' => auth()->id(),
            'layout' => $arenaLayout,
            'elements' => $arenaElements,
            'width' => $validated['arena_width'] ?? 800,
            'height' => $validated['arena_height'] ?? 600,
            'is_public' => false,
        ]);

        $match = VolleyballMatch::create([
            'home_team_name' => $validated['home_team_name'],
            'away_team_name' => $validated['away_team_name'],
            'start_time' => $start,
            'end_time' => $end,
            'players_per_team' => $validated['players_per_team'],
            'ticket_price' => $validated['ticket_price'] ?? 10.00,
            'is_local' => true,
            'status' => 'scheduled',
            'status_type' => 'upcoming',
            'home_coach' => $validated['home_coach'] ?? null,
            'away_coach' => $validated['away_coach'] ?? null,
            'judges' => $judgesArr,
            'location' => $validated['location'] ?? null,
            'arena_id' => $arena->id,
            'home_players' => $validated['home_players'],
            'away_players' => $validated['away_players'],
        ]);

        if ($request->hasFile('home_logo')) {
            $match->home_logo = $request->file('home_logo')->store('match_logos', 'public');
        }
        if ($request->hasFile('away_logo')) {
            $match->away_logo = $request->file('away_logo')->store('match_logos', 'public');
        }
        $match->save();

        try {
            $match->generateSeats();
        } catch (\Throwable $e) {
            \Log::warning($e->getMessage());
        }

        return redirect()->route('local.matches.index')->with('success', 'Mačs izveidots.');
    }

    public function adminEdit($id)
    {
        $match = VolleyballMatch::findOrFail($id);

        $prefill = [
            'home_team_name'   => $match->home_team_name,
            'away_team_name'   => $match->away_team_name,
            'start_time'       => $match->start_time?->format('Y-m-d\TH:i'),
            'end_time'         => $match->end_time?->format('Y-m-d\TH:i'),
            'players_per_team' => $match->players_per_team,
            'home_players'     => json_decode($match->home_players, true) ?? [],
            'away_players'     => json_decode($match->away_players, true) ?? [],
            'ticket_price'     => $match->ticket_price,
            'match_id'         => $match->id,
            'home_coach'       => $match->home_coach,
            'away_coach'       => $match->away_coach,
            'judges'           => $match->judges,
            'location'         => $match->location,
            'home_logo'        => $match->home_logo,
            'away_logo'        => $match->away_logo,
        ];

        return view('admin.matches.edit', compact('match', 'prefill'));
    }

    public function adminUpdate(Request $request, $id)
    {
        $match = VolleyballMatch::findOrFail($id);

        $validated = $request->validate([
            'home_team_name' => 'required|string|max:255',
            'away_team_name' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
            'players_per_team' => 'required|integer|in:2,4,6',
            'ticket_price' => 'nullable|numeric|min:0',
            'home_coach' => 'nullable|string|max:255',
            'away_coach' => 'nullable|string|max:255',
            'judges' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
            'home_logo' => 'nullable|file|image|mimes:jpg,jpeg,png,svg|max:4096',
            'away_logo' => 'nullable|file|image|mimes:jpg,jpeg,png,svg|max:4096',
        ]);

        $start = Carbon::parse($validated['start_time']);
        $end = isset($validated['end_time']) ? Carbon::parse($validated['end_time']) : $start->copy()->addHours(2);

        if ($this->hasTimeOverlap($start->toDateTimeString(), $end->toDateTimeString(), $match->id)) {
            return back()->withInput()->withErrors(['start_time' => 'Cits mačs pārklājas ar šo laiku.']);
        }

        $judgesArr = [];
        if (!empty($validated['judges'])) {
            $parts = array_map('trim', explode(',', $validated['judges']));
            $judgesArr = array_values(array_filter($parts, fn($x) => $x !== ''));
        }

        $updateData = array_merge($validated, [
            'start_time' => $start,
            'end_time' => $end,
            'home_players' => json_encode($request->input('home_players', [])),
            'away_players' => json_encode($request->input('away_players', [])),
            'home_coach' => $validated['home_coach'] ?? null,
            'away_coach' => $validated['away_coach'] ?? null,
            'judges' => $judgesArr,
            'location' => $validated['location'] ?? null,
        ]);

        if ($request->hasFile('home_logo')) {
            if ($match->home_logo) Storage::disk('public')->delete($match->home_logo);
            $updateData['home_logo'] = $request->file('home_logo')->store('match_logos', 'public');
        }
        if ($request->hasFile('away_logo')) {
            if ($match->away_logo) Storage::disk('public')->delete($match->away_logo);
            $updateData['away_logo'] = $request->file('away_logo')->store('match_logos', 'public');
        }

        $match->update($updateData);

        return redirect()->route('local.matches.index')->with('success', 'Mačs atjaunināts.');
    }

    public function adminDestroy($id)
    {
        $match = VolleyballMatch::findOrFail($id);
        if ($match->tickets()->where('status', 'paid')->exists()) {
            return back()->with('error', 'Nevar dzēst maču ar pārdotām biļetēm.');
        }

        if ($match->home_logo) Storage::disk('public')->delete($match->home_logo);
        if ($match->away_logo) Storage::disk('public')->delete($match->away_logo);

        $match->delete();

        return back()->with('success', 'Mačs dzēsts.');
    }

    public function localShow($id)
    {
        $match = VolleyballMatch::with('arena')->where('is_local', true)->findOrFail($id);
        $match->home_players = is_array($match->home_players) ? $match->home_players : (json_decode($match->home_players ?? '[]', true) ?: []);
        $match->away_players = is_array($match->away_players) ? $match->away_players : (json_decode($match->away_players ?? '[]', true) ?: []);

        $takenSeats = $match->seats()->whereNotNull('ticket_id')->pluck('seat_number')->toArray();
        $seatPrices = $match->seats()->pluck('price', 'seat_number')->toArray();
        $takenSeatIds = $match->seats()->whereNotNull('ticket_id')->pluck('id', 'seat_number')->toArray();
        $seatIdMap = $match->seats()->pluck('id', 'seat_number')->toArray();

        $seatPriceValues = array_values(array_filter($seatPrices, fn($p) => $p > 0));
        $minSeatPrice = !empty($seatPriceValues) ? min($seatPriceValues) : ($match->ticket_price ?? 0);
        $maxSeatPrice = !empty($seatPriceValues) ? max($seatPriceValues) : ($match->ticket_price ?? 0);

        $arena = $match->arena;
        $customElements = $arena ? $arena->elements : null;

        $newUserPromoPercent = 0;
        if (auth()->check()) {
            $paidTicketCount = Ticket::where('user_id', auth()->id())->where('status', 'paid')->count();
            if ($paidTicketCount < 3) {
                $newUserPromoPercent = 20;
            }
        }

        return view('matches.local.local_matches', compact(
            'match', 'takenSeats', 'seatPrices', 'takenSeatIds', 'seatIdMap', 'arena', 'customElements', 'newUserPromoPercent',
            'minSeatPrice', 'maxSeatPrice'
        ));
    }

    public function submitScoreRequest(Request $request, $id)
    {
        $match = VolleyballMatch::findOrFail($id);

        $validated = $request->validate([
            'sets' => 'required|array|min:3|max:5',
            'sets.*.home' => 'required|integer|min:0|max:100',
            'sets.*.away' => 'required|integer|min:0|max:100',
            'actual_end_time' => 'nullable|date|after_or_equal:start_time',
        ]);

        $sets = $validated['sets'];
        $homeWins = 0; $awayWins = 0;
        foreach ($sets as $s) {
            if ($s['home'] > $s['away']) $homeWins++;
            elseif ($s['away'] > $s['home']) $awayWins++;
        }

        $ver = MatchScoreVerification::create([
            'match_id' => $match->id,
            'user_id' => auth()->id(),
            'home_score' => $homeWins,
            'away_score' => $awayWins,
            'status' => 'pending',
            'approvals' => 0,
            'confirmations' => ['sets' => $sets],
        ]);

        if (!empty($validated['actual_end_time'])) {
            $match->actual_end_time = $validated['actual_end_time'];
            $match->save();
        }

        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new ScoreUpdateRequested($ver));

        return back()->with('success', 'Rezultāta pieprasījums nosūtīts.');
    }

    public function finalizeScore(Request $request, $id)
    {
        $match = VolleyballMatch::findOrFail($id);
        if (auth()->user()->role !== 'admin') abort(403);

        $validated = $request->validate([
            'sets' => 'required|array|min:3|max:5',
            'sets.*.home' => 'required|integer|min:0|max:100',
            'sets.*.away' => 'required|integer|min:0|max:100',
            'actual_end_time' => 'nullable|date|after_or_equal:start_time',
        ]);

        $sets = $validated['sets'];
        $homeWins = 0; $awayWins = 0;

        VolleyballMatchSet::where('match_id', $match->id)->delete();

        $setNumber = 1;
        foreach ($sets as $s) {
            VolleyballMatchSet::create([
                'match_id' => $match->id,
                'set_number' => $setNumber,
                'home_score' => $s['home'],
                'away_score' => $s['away'],
                'completed' => true,
            ]);
            if ($s['home'] > $s['away']) $homeWins++;
            elseif ($s['away'] > $s['home']) $awayWins++;
            $setNumber++;
        }

        $match->update([
            'home_score' => $homeWins,
            'away_score' => $awayWins,
            'actual_end_time' => $validated['actual_end_time'] ?? now(),
            'match_state' => 'completed',
            'status_type' => 'completed',
        ]);

        MatchScoreVerification::where('match_id', $match->id)
            ->where('home_score', $match->home_score)
            ->where('away_score', $match->away_score)
            ->update(['status' => 'finalized', 'approved' => true]);

        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new MatchFinalized($match));

        $this->resolvePredictions($match);

        return back()->with('success', 'Rezultāts apstiprināts.');
    }
    protected function resolvePredictions(\App\Models\VolleyballMatch $match)
{
    $predictions = Prediction::where('match_id', $match->id)->where('status','pending')->get();

    foreach ($predictions as $p) {
        $user = $p->user;
        $correct = false;
        if ($match->home_score > $match->away_score && $p->prediction === 'home') $correct = true;
        if ($match->away_score > $match->home_score && $p->prediction === 'away') $correct = true;
        if ($match->home_score === $match->away_score && $p->prediction === 'draw') $correct = true;

        if ($correct) {
            $reward = floatval(config('app.prediction_base_reward', 5.00)) + (floatval($p->staked_coins) * 2.0);
            $wallet = \App\Models\Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
            $wallet->credit($reward, 'earned_prediction', $user->id, $p, 'Prediction reward');
            $p->update(['status' => 'won', 'reward' => $reward]);
        } else {
            $p->update(['status' => 'lost', 'reward' => 0.00]);
        }
    }
}


    public function uploadMedia(Request $request, $id)
    {
        $match = VolleyballMatch::findOrFail($id);

        $existingPhotos = MatchMedia::where('match_id', $match->id)->where('type', 'photo')->count();
        $maxPhotos = 8;
        if ($existingPhotos >= $maxPhotos) {
            return back()->with('error', "Nepieciešams: maksimāli {$maxPhotos} bildes uz maču.");
        }

        $validated = $request->validate([
            'file' => 'required|image|max:10240|mimes:jpg,jpeg,png,gif',
            'caption' => 'nullable|string|max:255',
        ]);

        $path = $request->file('file')->store('match_media', 'public');

        MatchMedia::create([
            'match_id' => $match->id,
            'user_id' => auth()->id(),
            'type' => 'photo',
            'path' => $path,
            'mime' => $request->file('file')->getClientMimeType(),
            'caption' => $validated['caption'] ?? 'Skati no spēles',
        ]);

        return back()->with('success', 'Attēls augšupielādēts.');
    }
}
