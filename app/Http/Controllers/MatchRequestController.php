<?php

namespace App\Http\Controllers;

use App\Models\Arena;
use App\Models\MatchRequest;
use App\Models\ProductRequest;
use App\Models\Team;
use App\Models\User;
use App\Models\VolleyballMatch;
use App\Models\MatchScoreVerification;
use App\Notifications\RequestStatusChanged;
use App\Notifications\RequestSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MatchRequestController extends Controller
{
    // Jauna mača pieteikuma izveides forma ar arēnām un komandām
    public function create()
    {
        $arenas = Arena::where('user_id', Auth::id())
            ->orWhere('is_public', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $teams = Team::where('user_id', Auth::id())->latest()->get();

        return view('match_requests.create', compact('arenas', 'teams'));
    }

    // Validē un saglabā mača pieteikumu, nosūta paziņojumu administratoram
    public function store(Request $request)
    {
        $rules = [
            'request_type' => 'nullable|in:create_match,score_update',
            'home_team' => ['required','string','max:255','regex:/^[A-Za-zĀāČčĒēĢģĪīĶķĻļŅņŠšŪūŽž\s\-]+$/u'],
            'away_team' => ['required','string','max:255','regex:/^[A-Za-zĀāČčĒēĢģĪīĶķĻļŅņŠšŪūŽž\s\-]+$/u'],
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'players_per_team' => 'required|integer|in:2,4,6',
            'home_players' => 'required|array',
            'away_players' => 'required|array',
            'home_players.*.first_name' => ['required','string','max:100','regex:/^[A-Za-zĀāČčĒēĢģĪīĶķĻļŅņŠšŪūŽž\s\-]+$/u'],
            'home_players.*.last_name' => ['required','string','max:100','regex:/^[A-Za-zĀāČčĒēĢģĪīĶķĻļŅņŠšŪūŽž\s\-]+$/u'],
            'away_players.*.first_name' => ['required','string','max:100','regex:/^[A-Za-zĀāČčĒēĢģĪīĶķĻļŅņŠšŪūŽž\s\-]+$/u'],
            'away_players.*.last_name' => ['required','string','max:100','regex:/^[A-Za-zĀāČčĒēĢģĪīĶķĻļŅņŠšŪūŽž\s\-]+$/u'],
            'home_coach' => ['nullable','string','max:255','regex:/^[A-Za-zĀāČčĒēĢģĪīĶķĻļŅņŠšŪūŽž\s\-]+$/u'],
            'away_coach' => ['nullable','string','max:255','regex:/^[A-Za-zĀāČčĒēĢģĪīĶķĻļŅņŠšŪūŽž\s\-]+$/u'],
            'judges' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
            'home_logo' => 'nullable|file|image|mimes:jpg,png,svg|max:2048',
            'away_logo' => 'nullable|file|image|mimes:jpg,png,svg|max:2048',
            'arena_name' => 'required|string|max:255',
            'arena_layout' => 'nullable',
            'arena_elements' => 'nullable',
            'arena_width' => 'nullable|integer|min:400|max:2000',
            'arena_height' => 'nullable|integer|min:300|max:1500',
            'ticket_price' => 'nullable|numeric|min:0',
        ];

        $messages = [
            'home_team.required' => 'Mājas komandas nosaukums ir obligāts.',
            'home_team.regex' => 'Mājas komandas nosaukumā nevar būt ciparu vai simbolu.',
            'home_team.max' => 'Mājas komandas nosaukums nedrīkst pārsniegt 255 rakstzīmes.',
            'away_team.required' => 'Viesu komandas nosaukums ir obligāts.',
            'away_team.regex' => 'Viesu komandas nosaukumā nevar būt ciparu vai simbolu.',
            'away_team.max' => 'Viesu komandas nosaukums nedrīkst pārsniegt 255 rakstzīmes.',
            'start_time.required' => 'Sākuma laiks ir obligāts.',
            'start_time.date' => 'Sākuma laiks jāievada derīgā datuma formātā.',
            'end_time.required' => 'Beigu laiks ir obligāts.',
            'end_time.date' => 'Beigu laiks jāievada derīgā datuma formātā.',
            'end_time.after' => 'Beigu laikam jābūt pēc sākuma laika.',
            'players_per_team.required' => 'Spēlētāju skaits komandā ir obligāts.',
            'players_per_team.in' => 'Spēlētāju skaits var būt tikai 2, 4 vai 6.',
            'home_players.required' => 'Jānorāda mājas komandas spēlētāji.',
            'away_players.required' => 'Jānorāda viesu komandas spēlētāji.',
            'home_players.*.first_name.required' => 'Mājas komandas spēlētāja vārds ir obligāts.',
            'home_players.*.first_name.regex' => 'Mājas komandas spēlētāja vārds nevar saturēt ciparus vai simbolus.',
            'home_players.*.last_name.required' => 'Mājas komandas spēlētāja uzvārds ir obligāts.',
            'home_players.*.last_name.regex' => 'Mājas komandas spēlētāja uzvārds nevar saturēt ciparus vai simbolus.',
            'away_players.*.first_name.required' => 'Viesu komandas spēlētāja vārds ir obligāts.',
            'away_players.*.first_name.regex' => 'Viesu komandas spēlētāja vārds nevar saturēt ciparus vai simbolus.',
            'away_players.*.last_name.required' => 'Viesu komandas spēlētāja uzvārds ir obligāts.',
            'away_players.*.last_name.regex' => 'Viesu komandas spēlētāja uzvārds nevar saturēt ciparus vai simbolus.',
            'home_coach.regex' => 'Mājas komandas trenerim nevar būt ciparu vai simbolu.',
            'away_coach.regex' => 'Viesu komandas trenerim nevar būt ciparu vai simbolu.',
            'home_logo.mimes' => 'Mājas komandas logo jābūt jpg, png vai svg formātā.',
            'home_logo.max' => 'Mājas komandas logo nedrīkst pārsniegt 2 MB.',
            'away_logo.mimes' => 'Viesu komandas logo jābūt jpg, png vai svg formātā.',
            'away_logo.max' => 'Viesu komandas logo nedrīkst pārsniegt 2 MB.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        $validator->after(function ($v) use ($request) {
            $n = (int) $request->input('players_per_team', 0);
            if (count($request->input('home_players', [])) !== $n) {
                $v->errors()->add('home_players', "Jānorāda tieši {$n} spēlētājus mājas komandai.");
            }
            if (count($request->input('away_players', [])) !== $n) {
                $v->errors()->add('away_players', "Jānorāda tieši {$n} spēlētājus viesu komandai.");
            }
        });

        $validated = $validator->validate();

        $start = Carbon::parse($validated['start_time']);
        $end = Carbon::parse($validated['end_time']);

        if ($start->lessThan(Carbon::now()->addMinutes(5))) {
            return back()->withInput()->withErrors([
                'start_time' => 'Sākuma laiks jābūt vismaz 5 minūtes nākotnē.'
            ]);
        }


$arenaLayout = $validated['arena_layout'] ?? [];
        if (is_string($arenaLayout)) {
            $arenaLayout = json_decode($arenaLayout, true) ?: [];
        }
        $arenaElements = $validated['arena_elements'] ?? [];
        if (is_string($arenaElements)) {
            $arenaElements = json_decode($arenaElements, true) ?: [];
        }

        $data = array_merge($validated, [ 
            'user_id' => Auth::id(),
            'status' => 'pending',
            'judges' => !empty($validated['judges'])
                ? array_values(array_filter(array_map('trim', explode(',', $validated['judges']))))
                : [],
            'home_players' => $validated['home_players'],
            'away_players' => $validated['away_players'],
            'arena_layout' => json_encode($arenaLayout),
            'arena_elements' => json_encode($arenaElements),
            'arena_width' => $validated['arena_width'] ?? 800,
            'arena_height' => $validated['arena_height'] ?? 600,
        ]);

        if ($request->hasFile('home_logo')) {
            $data['home_logo'] = $request->file('home_logo')->store('match_logos', 'public');
        }
        if ($request->hasFile('away_logo')) {
            $data['away_logo'] = $request->file('away_logo')->store('match_logos', 'public');
        }

        $matchReq = MatchRequest::create($data);

        // Notify all admins about the new request
        $summary = ($data['home_team'] ?? '') . ' vs ' . ($data['away_team'] ?? '');
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new RequestSubmitted(
                $data['request_type'] ?? 'create_match',
                $matchReq->id,
                Auth::user()->name,
                $summary
            ));
        }

        return redirect()->route('match_requests.my')
            ->with('success', 'Jūsu mača pieprasījums nosūtīts administratoram.');
    }

    // Rāda lietotāja mača un produktu pieteikumu sarakstu
    public function myRequests(Request $request)
{
    $matchRequests = MatchRequest::where('user_id', Auth::id())
        ->orderBy('created_at', 'desc')
        ->get();

    $productRequests = ProductRequest::where('user_id', Auth::id())
        ->orderBy('created_at', 'desc')
        ->get();

   
    $requests = collect();

    foreach ($matchRequests as $m) {
        $m->type = $m->request_type === 'score_update' ? 'score_update' : 'match';
        $requests->push($m);
    }

    foreach ($productRequests as $p) {
        $p->type = 'product';
        $requests->push($p);
    }

    // Kārto un lapo apvienotos pieteikumus
    $requests = $requests->sortByDesc(fn($r) => $r->created_at)->values();

    $perPage = 10;
    $page = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
    $itemsForPage = $requests->slice(($page - 1) * $perPage, $perPage)->values();

    $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
        $itemsForPage,
        $requests->count(),
        $perPage,
        $page,
        ['path' => url()->current(), 'query' => request()->query()]
    );

    return view('match_requests.index', ['requests' => $paginated]);
}


    // Rāda konkrētu lietotāja pieteikumu
    public function view($id)
    {
        $requestData = MatchRequest::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('match_requests.view', ['request' => $requestData]);
    }

    // Rediģēšanas forma gaidošam mača pieteikumam
    public function edit($id)
    {
        $requestData = MatchRequest::where('id', $id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->firstOrFail();

        $arenas = Arena::where('user_id', Auth::id())
            ->orWhere('is_public', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $teams = Team::where('user_id', Auth::id())->latest()->get();

        return view('match_requests.edit', ['request' => $requestData, 'arenas' => $arenas, 'teams' => $teams]);
    }

    // Atjaunina gaidošo mača pieteikumu
    public function update(Request $request, MatchRequest $matchRequest)
    {
        if ($matchRequest->user_id !== auth()->id()) {
            abort(403);
        }

        $rules = [
            'home_team' => ['required','string','max:255','regex:/^[A-Za-zĀāČčĒēĢģĪīĶķĻļŅņŠšŪūŽž\s\-]+$/u'],
            'away_team' => ['required','string','max:255','regex:/^[A-Za-zĀāČčĒēĢģĪīĶķĻļŅņŠšŪūŽž\s\-]+$/u'],
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'players_per_team' => 'required|integer|in:2,4,6',
            'home_players.*.first_name' => ['required','string','max:100','regex:/^[A-Za-zĀāČčĒēĢģĪīĶķĻļŅņŠšŪūŽž\s\-]+$/u'],
            'home_players.*.last_name' => ['required','string','max:100','regex:/^[A-Za-zĀāČčĒēĢģĪīĶķĻļŅņŠšŪūŽž\s\-]+$/u'],
            'away_players.*.first_name' => ['required','string','max:100','regex:/^[A-Za-zĀāČčĒēĢģĪīĶķĻļŅņŠšŪūŽž\s\-]+$/u'],
            'away_players.*.last_name' => ['required','string','max:100','regex:/^[A-Za-zĀāČčĒēĢģĪīĶķĻļŅņŠšŪūŽž\s\-]+$/u'],
            'home_coach' => ['nullable','string','max:255','regex:/^[A-Za-zĀāČčĒēĢģĪīĶķĻļŅņŠšŪūŽž\s\-]+$/u'],
            'away_coach' => ['nullable','string','max:255','regex:/^[A-Za-zĀāČčĒēĢģĪīĶķĻļŅņŠšŪūŽž\s\-]+$/u'],
            'judges' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
            'home_logo' => 'nullable|file|image|mimes:jpg,png,svg|max:2048',
            'away_logo' => 'nullable|file|image|mimes:jpg,png,svg|max:2048',
        ];

        $messages = [
            'home_team.required' => 'Mājas komandas nosaukums ir obligāts.',
            'home_team.regex' => 'Mājas komandas nosaukumā nevar būt ciparu vai simbolu.',
            'away_team.required' => 'Viesu komandas nosaukums ir obligāts.',
            'away_team.regex' => 'Viesu komandas nosaukumā nevar būt ciparu vai simbolu.',
            'home_logo.mimes' => 'Mājas komandas logo jābūt jpg, png vai svg formātā.',
            'home_logo.max' => 'Mājas komandas logo nedrīkst pārsniegt 2 MB.',
            'away_logo.mimes' => 'Viesu komandas logo jābūt jpg, png vai svg formātā.',
            'away_logo.max' => 'Viesu komandas logo nedrīkst pārsniegt 2 MB.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        $validator->after(function ($v) use ($request) {
            $n = (int) $request->input('players_per_team', 0);
            if (count($request->input('home_players', [])) !== $n) {
                $v->errors()->add('home_players', "Jānorāda tieši {$n} spēlētājus mājas komandai.");
            }
            if (count($request->input('away_players', [])) !== $n) {
                $v->errors()->add('away_players', "Jānorāda tieši {$n} spēlētājus viesu komandai.");
            }
        });

        $validated = $validator->validate();

        $start = Carbon::parse($validated['start_time']);
        $end = Carbon::parse($validated['end_time']);

        if ($start->lessThan(Carbon::now()->addMinutes(5))) {
            return back()->withInput()->withErrors([
                'start_time' => 'Sākuma laiks jābūt vismaz 5 minūtes nākotnē.'
            ]);
        }


        $update = [
            'home_team' => $validated['home_team'],
            'away_team' => $validated['away_team'],
            'start_time' => $start,
            'end_time' => $end,
            'players_per_team' => $validated['players_per_team'],
            'home_players' => json_encode($request->input('home_players', [])),
            'away_players' => json_encode($request->input('away_players', [])),
            'home_coach' => $validated['home_coach'] ?? null,
            'away_coach' => $validated['away_coach'] ?? null,
            'judges' => !empty($validated['judges'])
                ? json_encode(array_values(array_filter(array_map('trim', explode(',', $validated['judges'])))))
                : json_encode([]),
            'location' => $validated['location'] ?? null,
            'ticket_price' => $validated['ticket_price'] ?? null,
        ];

        foreach (['home_logo', 'away_logo'] as $field) {
            if ($request->hasFile($field)) {
                if ($matchRequest->$field) {
                    Storage::disk('public')->delete($matchRequest->$field);
                }
                $update[$field] = $request->file($field)->store('match_logos', 'public');
            }
        }

        $matchRequest->update($update);

        return redirect()->route('match_requests.my')
            ->with('success', 'Mača pieprasījums atjaunināts.');
    }

    // Atceļ gaidošo mača pieteikumu un dzēš logotipus
    public function cancel($id)
    {
        $req = MatchRequest::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($req->status !== 'pending') {
            return back()->with('error', 'Tikai neapstiprinātus pieprasījumus var atcelt.');
        }

        $req->update(['status' => 'cancelled']);
        return back()->with('success', 'Jūsu mača pieprasījums ir atcelts.');
    }

    // Dzēš noraidītu vai pieņemtu pieteikumu no vēstures
    public function destroy($id)
    {
        $req = MatchRequest::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (!in_array($req->status, ['rejected', 'accepted', 'cancelled'])) {
            return back()->with('error', 'Šo pieprasījumu nevar dzēst.');
        }

        if ($req->home_logo) Storage::disk('public')->delete($req->home_logo);
        if ($req->away_logo) Storage::disk('public')->delete($req->away_logo);

        $req->delete();
        return redirect()->route('match_requests.my')->with('success', 'Pieprasījums dzēsts.');
    }

    // Dzēš pieteikumu kā administrators
    public function adminDestroy($id)
    {
        $req = MatchRequest::findOrFail($id);

        if ($req->home_logo) Storage::disk('public')->delete($req->home_logo);
        if ($req->away_logo) Storage::disk('public')->delete($req->away_logo);

        $req->delete();
        return redirect()->route('admin.match_requests.inbox')->with('success', 'Pieprasījums dzēsts.');
    }

    // Admin iesūtne — rāda visus gaidošos mača un produktu pieteikumus ar filtriem
    public function inbox(Request $request)
    {
        $statusFilter = $request->filled('status') ? $request->status : null;

        $matchReqQ = \App\Models\MatchRequest::with('user')->select('*');
        $prodReqQ  = \App\Models\ProductRequest::with('user')->select('*');

        // Default: only pending unless a status filter is explicitly set
        if ($statusFilter && in_array($statusFilter, ['pending', 'accepted', 'rejected'])) {
            $matchReqQ->where('status', $statusFilter);
            $prodReqQ->where('status', $statusFilter);
        } else {
            $matchReqQ->where('status', 'pending');
            $prodReqQ->where('status', 'pending');
        }

        $type = $request->filled('type') ? $request->type : null;

        if ($type === 'match' || $type === 'score_update') {

            if ($type === 'match') {
                $matchReqQ->where('request_type', 'create_match');
            } elseif ($type === 'score_update') {
                $matchReqQ->where('request_type', 'score_update');
            }
            $includeProducts = false;
        } elseif ($type === 'product') {
            $includeProducts = true;
            $matchReqQ = $matchReqQ->whereRaw('0 = 1'); 
        } else {
            $includeProducts = true;
        }

        if ($request->filled('user')) {
            $search = $request->user;
            $matchReqQ->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            $prodReqQ->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        if ($request->filled('start_date')) {
            $matchReqQ->whereDate('start_time', '>=', $request->start_date);
            $prodReqQ->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $matchReqQ->whereDate('start_time', '<=', $request->end_date);
            $prodReqQ->whereDate('created_at', '<=', $request->end_date);
        }

        $matchRequests = $matchReqQ->orderBy('created_at', 'desc')->get();
        $productRequests = $includeProducts ? $prodReqQ->orderBy('created_at', 'desc')->get() : collect();

        $normalized = collect();

        foreach ($matchRequests as $m) {
            $m->inbox_type = ($m->request_type === 'score_update') ? 'score_update' : 'match';
            $normalized->push($m);
        }

        foreach ($productRequests as $p) {
            $p->inbox_type = 'product';
            $normalized->push($p);
        }

        $sorted = $normalized->sortByDesc(fn($item) => $item->created_at ?? $item->start_time ?? now());

        $perPage = 20;
        $page = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $itemsForCurrentPage = $sorted->slice(($page - 1) * $perPage, $perPage)->values();

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $itemsForCurrentPage,
            $sorted->count(),
            $perPage,
            $page,
            ['path' => url()->current(), 'query' => request()->query()]
        );

        return view('admin.match_requests.inbox', ['requests' => $paginated]);
    }

    // Rāda konkrētu pieteikumu (novirza uz produkta pieteikumu ja nepieciešams)
   public function show($id)
{
    $req = MatchRequest::with('user')->find($id);
    if ($req) {
        if ($req->request_type === 'score_update') {
            $verification = $req->match_id
                ? MatchScoreVerification::where('match_id', $req->match_id)->latest()->first()
                : null;
            return view('admin.match_requests.show_score_update', compact('req', 'verification'));
        }
        return view('admin.match_requests.show', compact('req'));
    }

    $prod = ProductRequest::with('user')->find($id);
    if ($prod) {
        return redirect()->route('admin.product_requests.show', $prod->id);
    }

    abort(404);
}



    // Apstiprina mača pieteikumu un paziņo iesniedzēju
    public function accept($id)
    {
        $req = MatchRequest::findOrFail($id);
        $req->update(['status' => 'accepted']);

        // Notify the user
        if ($req->user) {
            $summary = ($req->home_team ?? '') . ' vs ' . ($req->away_team ?? '');
            $req->user->notify(new RequestStatusChanged($req->id, 'accepted', $summary));
        }

        // Score-update requests should finalize the match score, not open create page
        if ($req->request_type === 'score_update' && $req->match_id) {
            $match = VolleyballMatch::find($req->match_id);
            if ($match) {
                $match->update([
                    'home_score'  => $req->score_home ?? 0,
                    'away_score'  => $req->score_away ?? 0,
                    'match_state' => 'completed',
                ]);
            }
            return redirect()
                ->route('admin.match_requests.inbox')
                ->with('success', 'Rezultāts apstiprināts un mačs atzīmēts kā pabeigts.');
        }

        return redirect()
            ->route('admin.matches.create', ['request_id' => $req->id])
            ->with('success', 'Pieprasījums apstiprināts — rediģējiet maču un pievienojiet cenu.');
    }

    // Noraida mača pieteikumu ar iemeslu un paziņo iesniedzēju
   public function reject($id, Request $request)
{
    \Log::info("Reject endpoint hit", ['id' => $id, 'user_id' => auth()->id()]);

    $req = MatchRequest::find($id);
    if (! $req) {
        \Log::warning("MatchRequest not found for reject", ['id' => $id]);
        return redirect()->route('admin.match_requests.inbox')->with('error', "Pieprasījums #{$id} nav atrasts.");
    }

    $reason = $request->input('rejection_reason');
    $req->update([
        'status'           => 'rejected',
        'rejection_reason' => $reason,
    ]);

    if ($req->user) {
        $summary = ($req->home_team ?? '') . ' vs ' . ($req->away_team ?? '');
        $req->user->notify(new RequestStatusChanged($req->id, 'rejected', $summary, $reason));
    }

    return redirect()->route('admin.match_requests.inbox')->with('success', 'Pieprasījums noraidīts.');
}

    // Atzīmē pieteikumu kā "tiek izskatīts" un paziņo iesniedzēju
    public function markReviewing($id)
    {
        $req = MatchRequest::findOrFail($id);
        $req->update(['status' => 'reviewing']);

        if ($req->user) {
            $summary = ($req->home_team ?? '') . ' vs ' . ($req->away_team ?? '');
            $req->user->notify(new RequestStatusChanged($req->id, 'reviewing', $summary));
        }

        return redirect()->route('admin.match_requests.show', $id)
            ->with('success', 'Pieprasījums atzīmēts kā "tiek izskatīts".');
    }

    // Iesniedz apelāciju par noraidītu pieteikumu
    public function submitAppeal(Request $request, $id)
    {
        $req = MatchRequest::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('status', 'rejected')
            ->firstOrFail();

        $request->validate([
            'appeal_message' => 'required|string|max:2000',
        ], ['appeal_message.required' => 'Lūdzu ievadiet apelācijas ziņojumu.']);

        $req->update([
            'status'         => 'appealed',
            'appeal_message' => $request->input('appeal_message'),
        ]);

        $summary = ($req->home_team ?? '') . ' vs ' . ($req->away_team ?? '');
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new RequestSubmitted('appeal', $req->id, Auth::user()->name, "Apelācija: {$summary}"));
        }

        return redirect()->route('match_requests.view', $id)
            ->with('success', 'Jūsu apelācija iesniegta.');
    }

}
