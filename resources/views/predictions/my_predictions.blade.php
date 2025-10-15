<x-app-layout>
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Manas prognozes (All My Predictions)</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4">{{ session('error') }}</div>
    @endif

    @if($predictions->isEmpty())
        <div class="text-gray-500">You have not placed any predictions yet.</div>
    @else
        <div class="space-y-4">
            @foreach($predictions as $p)
                @php $m = $matches[$p->match_id] ?? null; @endphp
                <div class="border rounded-lg shadow p-4 bg-white">
                    <div class="flex justify-between items-start">
                        <div>
                            @if($m)
                                <div class="font-semibold">{{ $m->home_team_name }} {{ $m->home_score ?? '-' }} - {{ $m->away_score ?? '-' }} {{ $m->away_team_name }}</div>
                                <div class="text-gray-500 text-sm">{{ \Carbon\Carbon::parse($m->start_time)->format('d M Y, H:i') }}</div>
                            @else
                                <div class="font-semibold">Match #{{ $p->match_id }}</div>
                            @endif

                            <div class="mt-2 text-sm">
                                Pick: <strong>{{ ucfirst($p->prediction) }}</strong>
                                | Staked: <strong>{{ $p->staked_coins ?? 0 }}</strong> ⚪
                                | Status: <strong>{{ ucfirst($p->status) }}</strong>
                                @if($p->reward !== null) | Reward: <strong>{{ $p->reward }}</strong> ⚪ @endif
                            </div>
                        </div>

                        <div class="text-right">
                            {{-- Allow quick edit: if match hasn't started, link back to predictions page (they can update there) --}}
                            @if($m && now()->lt(\Carbon\Carbon::parse($m->start_time)))
                                <div class="text-sm mb-2"><a href="{{ route('predictions.index') }}#match-{{ $m->id }}" class="text-blue-600 hover:underline">Edit</a></div>
                            @endif
                            <div class="text-sm text-gray-500">{{ $p->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
</x-app-layout>
