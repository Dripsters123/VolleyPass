<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeamController extends Controller
{
    public function index()
    {
        $teams = auth()->user()->teams()->latest()->get();
        return view('teams.index', compact('teams'));
    }

    public function create()
    {
        return view('teams.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'coach'           => 'nullable|string|max:100',
            'players_per_team'=> 'required|in:2,4,6',
            'players'         => 'required|array',
            'players.*.first_name' => 'required|string|max:60',
            'players.*.last_name'  => 'required|string|max:60',
            'logo'            => 'nullable|image|max:2048',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('team_logos', 'public');
        }

        Team::create([
            'user_id'          => auth()->id(),
            'name'             => $data['name'],
            'coach'            => $data['coach'] ?? null,
            'players_per_team' => $data['players_per_team'],
            'players'          => $data['players'],
            'logo'             => $logoPath,
        ]);

        return redirect()->route('teams.index')->with('success', 'Komanda izveidota!');
    }

    public function edit(Team $team)
    {
        abort_unless($team->user_id === auth()->id(), 403);
        return view('teams.edit', compact('team'));
    }

    public function update(Request $request, Team $team)
    {
        abort_unless($team->user_id === auth()->id(), 403);

        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'coach'           => 'nullable|string|max:100',
            'players_per_team'=> 'required|in:2,4,6',
            'players'         => 'required|array',
            'players.*.first_name' => 'required|string|max:60',
            'players.*.last_name'  => 'required|string|max:60',
            'logo'            => 'nullable|image|max:2048',
        ]);

        $logoPath = $team->logo;
        if ($request->hasFile('logo')) {
            if ($logoPath) Storage::disk('public')->delete($logoPath);
            $logoPath = $request->file('logo')->store('team_logos', 'public');
        }

        $team->update([
            'name'             => $data['name'],
            'coach'            => $data['coach'] ?? null,
            'players_per_team' => $data['players_per_team'],
            'players'          => $data['players'],
            'logo'             => $logoPath,
        ]);

        return redirect()->route('teams.index')->with('success', 'Komanda atjaunināta!');
    }

    public function destroy(Team $team)
    {
        abort_unless($team->user_id === auth()->id(), 403);
        if ($team->logo) Storage::disk('public')->delete($team->logo);
        $team->delete();
        return back()->with('success', 'Komanda dzēsta.');
    }

    /**
     * API endpoint: return user's teams as JSON (for match request form).
     */
    public function api()
    {
        $teams = auth()->user()->teams()->latest()->get(['id', 'name', 'players_per_team', 'players', 'coach', 'logo']);
        return response()->json($teams);
    }
}
