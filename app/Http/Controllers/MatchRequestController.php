<?php

namespace App\Http\Controllers;

use App\Models\MatchRequest;
use App\Models\ProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MatchRequestController extends Controller
{
    protected function hasTimeOverlapWithMatches($startIso, $endIso): bool
    {
        $start = Carbon::parse($startIso);
        $end = Carbon::parse($endIso);

        return \App\Models\VolleyballMatch::where('is_local', true)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->exists();
    }


    public function create()
    {
        return view('match_requests.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'request_type' => 'nullable|in:create_match,score_update',
            'home_team' => ['required','string','max:255','regex:/^[A-Za-z\s]+$/'],
            'away_team' => ['required','string','max:255','regex:/^[A-Za-z\s]+$/'],
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'players_per_team' => 'required|integer|in:2,4,6',
            'home_players' => 'required|array',
            'away_players' => 'required|array',
            'home_players.*.first_name' => ['required','string','max:100','regex:/^[A-Za-z\s]+$/'],
            'home_players.*.last_name' => ['required','string','max:100','regex:/^[A-Za-z\s]+$/'],
            'away_players.*.first_name' => ['required','string','max:100','regex:/^[A-Za-z\s]+$/'],
            'away_players.*.last_name' => ['required','string','max:100','regex:/^[A-Za-z\s]+$/'],
            'home_coach' => ['nullable','string','max:255','regex:/^[A-Za-z\s]+$/'],
            'away_coach' => ['nullable','string','max:255','regex:/^[A-Za-z\s]+$/'],
            'judges' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
            'home_logo' => 'nullable|file|image|mimes:jpg,png,svg|max:2048',
            'away_logo' => 'nullable|file|image|mimes:jpg,png,svg|max:2048',
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

        if ($this->hasTimeOverlapWithMatches($start, $end)) {
            return back()->withInput()->withErrors([
                'start_time' => 'Šajā laikā stadionā jau ir plānots mačs. Lūdzu izvēlieties citu laiku.'
            ]);
        }

        $data = array_merge($validated, [
            'user_id' => Auth::id(),
            'status' => 'pending',
            'judges' => !empty($validated['judges'])
                ? json_encode(array_values(array_filter(array_map('trim', explode(',', $validated['judges'])))))
                : json_encode([]),
            'home_players' => json_encode($validated['home_players']),
            'away_players' => json_encode($validated['away_players']),
        ]);

        if ($request->hasFile('home_logo')) {
            $data['home_logo'] = $request->file('home_logo')->store('match_logos', 'public');
        }
        if ($request->hasFile('away_logo')) {
            $data['away_logo'] = $request->file('away_logo')->store('match_logos', 'public');
        }

        MatchRequest::create($data);

        return redirect()->route('match_requests.my')
            ->with('success', 'Jūsu mača pieprasījums nosūtīts administratoram.');
    }

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


    public function view($id)
    {
        $requestData = MatchRequest::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('match_requests.view', ['request' => $requestData]);
    }

    public function edit($id)
    {
        $requestData = MatchRequest::where('id', $id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->firstOrFail();

        return view('match_requests.edit', ['request' => $requestData]);
    }

    public function update(Request $request, MatchRequest $matchRequest)
    {
        if ($matchRequest->user_id !== auth()->id()) {
            abort(403);
        }

        $rules = [
            'home_team' => ['required','string','max:255','regex:/^[A-Za-z\s]+$/'],
            'away_team' => ['required','string','max:255','regex:/^[A-Za-z\s]+$/'],
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'players_per_team' => 'required|integer|in:2,4,6',
            'home_players.*.first_name' => ['required','string','max:100','regex:/^[A-Za-z\s]+$/'],
            'home_players.*.last_name' => ['required','string','max:100','regex:/^[A-Za-z\s]+$/'],
            'away_players.*.first_name' => ['required','string','max:100','regex:/^[A-Za-z\s]+$/'],
            'away_players.*.last_name' => ['required','string','max:100','regex:/^[A-Za-z\s]+$/'],
            'home_coach' => ['nullable','string','max:255','regex:/^[A-Za-z\s]+$/'],
            'away_coach' => ['nullable','string','max:255','regex:/^[A-Za-z\s]+$/'],
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

        if ($this->hasTimeOverlapWithMatches($start, $end)) {
            return back()->withInput()->withErrors([
                'start_time' => 'Šajā laikā stadionā jau ir plānots mačs. Lūdzu izvēlieties citu laiku.'
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

    public function cancel($id)
    {
        $req = MatchRequest::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($req->status !== 'pending') {
            return back()->with('error', 'Tikai neapstiprinātus pieprasījumus var atcelt.');
        }

        if ($req->home_logo) {
            Storage::disk('public')->delete($req->home_logo);
        }
        if ($req->away_logo) {
            Storage::disk('public')->delete($req->away_logo);
        }

        $req->delete();
        return back()->with('success', 'Jūsu mača pieprasījums ir atcelts.');
    }

    public function inbox(Request $request)
    {
        $matchReqQ = \App\Models\MatchRequest::with('user')->where('status', 'pending')->select('*');
        $prodReqQ  = \App\Models\ProductRequest::with('user')->where('status', 'pending')->select('*');

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
            $matchReqQ->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
            $prodReqQ->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
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

   public function show($id)
{
  
    $req = MatchRequest::with('user')->find($id);
    if ($req) {
        
        if ($req->request_type === 'score_update') {
            return view('admin.match_requests.show_score_update', compact('req'));
        }
        
        return view('admin.match_requests.show', compact('req'));
    }


    $prod = ProductRequest::with('user')->find($id);
    if ($prod) {
       
        return redirect()->route('admin.product_requests.show', $prod->id);
    }

    abort(404);
}



    public function accept($id)
    {
        $req = MatchRequest::findOrFail($id);
        $req->update(['status' => 'accepted']);

        return redirect()
            ->route('admin.matches.create', ['request_id' => $req->id])
            ->with('success', 'Pieprasījums apstiprināts — rediģējiet maču un pievienojiet cenu.');
    }

   public function reject($id, Request $request)
{
    \Log::info("Reject endpoint hit", ['id' => $id, 'user_id' => auth()->id()]);

    $req = MatchRequest::find($id);
    if (! $req) {
        \Log::warning("MatchRequest not found for reject", ['id' => $id]);
        return redirect()->route('admin.match_requests.inbox')->with('error', "Pieprasījums #{$id} nav atrasts.");
    }

    $req->update(['status' => 'rejected']);
    return redirect()->route('admin.match_requests.inbox')->with('success', 'Pieprasījums noraidīts.');
}


}
