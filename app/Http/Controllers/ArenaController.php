<?php

namespace App\Http\Controllers;

use App\Models\Arena;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArenaController extends Controller
{
    // Rāda lietotāja arēnas, izlasītās un noklusuma izkārtojumus
    public function index()
    {
        $userId = Auth::id();

        $myArenas = Arena::where('user_id', $userId)->orderBy('name')->get();

        $favorites = Arena::whereHas('favoritedBy', fn($q) => $q->where('user_id', $userId))
            ->orderBy('name')
            ->get();

        $defaultLayouts = Arena::where('is_public', true)
            ->whereHas('user', fn($q) => $q->where('role', 'admin'))
            ->orderBy('name')
            ->get();

        // Attach isFavorited flag for my arenas
        $myArenas->each(fn($a) => $a->setAttribute('is_favorited', $a->isFavoritedBy($userId)));
        $favorites->each(fn($a) => $a->setAttribute('is_favorited', true));
        $defaultLayouts->each(fn($a) => $a->setAttribute('is_favorited', $a->isFavoritedBy($userId)));

        return view('arenas.index', compact('myArenas', 'favorites', 'defaultLayouts'));
    }

    // Jaunas arēnas izveides forma
    public function create()
    {
        return view('arenas.create');
    }

    // Saglabā jauno arēnu datu bāzē
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'layout' => 'required',
            'elements' => 'required',
            'width' => 'required|integer|min:400|max:2000',
            'height' => 'required|integer|min:300|max:1500',
            'is_public' => 'boolean',
            'width_m' => 'nullable|numeric|min:1|max:200',
            'height_m' => 'nullable|numeric|min:1|max:200',
        ]);

        $layout = $request->input('layout', []);
        if (is_string($layout)) {
            $layout = json_decode($layout, true);
        }
        $elements = $request->input('elements', []);
        if (is_string($elements)) {
            $elements = json_decode($elements, true);
        }

        if (!is_array($layout)) {
            $layout = [];
        }
        if (!is_array($elements)) {
            $elements = [];
        }

        $arena = Arena::create([
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => Auth::id(),
            'width' => $request->width,
            'height' => $request->height,
            'is_public' => $request->boolean('is_public', false),
            'layout' => $layout,
            'elements' => $elements,
        ]);

        return redirect()->route('arenas.edit', $arena)->with('success', 'Arēna veiksmīgi izveidota!');
    }

    // Rāda konkrētu arēnu
    public function show(Arena $arena)
    {
        $this->authorize('view', $arena);

        return view('arenas.show', compact('arena'));
    }

    // Arēnas rediģēšanas forma
    public function edit(Arena $arena)
    {
        $this->authorize('update', $arena);

        return view('arenas.edit', compact('arena'));
    }

    // Atjaunina arēnas datus (izkārtojumu, izmērus, nosaukumu)
    public function update(Request $request, Arena $arena)
    {
        $this->authorize('update', $arena);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'layout' => 'required',
            'elements' => 'required',
            'width' => 'required|integer|min:400|max:2000',
            'height' => 'required|integer|min:300|max:1500',
            'is_public' => 'boolean',
        ]);

        $layout = $request->input('layout', $arena->layout);
        if (is_string($layout)) {
            $layout = json_decode($layout, true);
        }
        $elements = $request->input('elements', $arena->elements);
        if (is_string($elements)) {
            $elements = json_decode($elements, true);
        }

        if (!is_array($layout)) {
            $layout = $arena->layout;
        }
        if (!is_array($elements)) {
            $elements = $arena->elements;
        }

        $arena->update([
            'name' => $request->name,
            'description' => $request->description,
            'layout' => $layout,
            'elements' => $elements,
            'width' => $request->width,
            'height' => $request->height,
            'is_public' => $request->boolean('is_public', $arena->is_public),
        ]);

        return response()->json(['success' => true, 'message' => 'Arēna veiksmīgi saglabāta!']);
    }

    // Dzēš arēnu
    public function destroy(Arena $arena)
    {
        $this->authorize('delete', $arena);

        $arena->delete();

        return redirect()->route('arenas.index')->with('success', 'Arēna veiksmīgi dzēsta!');
    }

    // Izveido arēnas kopiju
    public function duplicate(Arena $arena)
    {
        $this->authorize('view', $arena);

        $duplicate = Arena::create([
            'name' => $arena->name . ' (Kopija)',
            'description' => $arena->description,
            'user_id' => Auth::id(),
            'width' => $arena->width,
            'height' => $arena->height,
            'is_public' => false,
            'layout' => $arena->layout,
            'elements' => $arena->elements,
        ]);

        return redirect()->route('arenas.edit', $duplicate)->with('success', 'Arēna veiksmīgi dublēta!');
    }

    // Pievieno vai noņem arēnu no izlasēm
    public function toggleFavorite(Arena $arena)
    {
        $user = Auth::user();
        $arena->favoritedBy()->toggle($user->id);
        $isFavorited = $arena->favoritedBy()->where('user_id', $user->id)->exists();

        if (request()->wantsJson()) {
            return response()->json(['favorited' => $isFavorited]);
        }

        return back()->with('success', $isFavorited ? 'Pievienots izlasē.' : 'Noņemts no izlases.');
    }

    // Maina arēnas publisku/privātu statusu
    public function togglePublic(Arena $arena)
    {
        $this->authorize('update', $arena);
        $arena->update(['is_public' => !$arena->is_public]);

        if (request()->wantsJson()) {
            return response()->json(['is_public' => $arena->is_public]);
        }

        return back()->with('success', $arena->is_public ? 'Arēna ir publiska.' : 'Arēna ir privāta.');
    }
}
