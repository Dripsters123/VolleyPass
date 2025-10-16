{{-- resources/views/admin/match_requests/show_score_update.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Rezultāta pieprasījums — #{{ $req->id }}
                </h2>
                <div class="text-sm text-gray-500 mt-1">
                    Iesniedzējs: {{ optional($req->user)->name ?? '—' }} @if(optional($req->user)->email) ({{ optional($req->user)->email }}) @endif
                </div>
            </div>

            <div>
                <span class="inline-block px-3 py-1 rounded bg-yellow-100 text-yellow-800 text-sm">
                    {{ ucfirst($req->status ?? 'pending') }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">

                <h3 class="text-lg font-medium mb-4">Mača informācija</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <div class="text-sm text-gray-500">Mājas komanda</div>
                        <div class="font-semibold">{{ $req->home_team ?? ($req->home_team_name ?? '—') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Viesu komanda</div>
                        <div class="font-semibold">{{ $req->away_team ?? ($req->away_team_name ?? '—') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Sākums</div>
                        <div>
                            @if(isset($req->start_time))
                                {{ \Carbon\Carbon::parse($req->start_time)->timezone('Europe/Riga')->format('d.m.Y H:i') }}
                            @else
                                —
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Beigas</div>
                        <div>
                            @if(isset($req->end_time))
                                {{ \Carbon\Carbon::parse($req->end_time)->timezone('Europe/Riga')->format('d.m.Y H:i') }}
                            @else
                                —
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Score display: prefer verification (match_score_verifications) then fallback --}}
                @php
                    $scoreDisplay = null;
                    $scoreSource = null;

                    // 1) verification row (preferred)
                    if (isset($verification) && $verification) {
                        $h = $verification->home_score;
                        $a = $verification->away_score;
                        if (!is_null($h) || !is_null($a)) {
                            $scoreDisplay = trim(($h ?? '?') . ' - ' . ($a ?? '?'));
                            $scoreSource = 'verification';
                        }
                    }

                    // 2) explicit request fields
                    if (! $scoreDisplay) {
                        if (!empty($req->requested_home_score) || !empty($req->requested_away_score)) {
                            $scoreDisplay = (($req->requested_home_score ?? '?') . ' - ' . ($req->requested_away_score ?? '?'));
                            $scoreSource = 'request_fields';
                        } elseif (!empty($req->requested_score)) {
                            $scoreDisplay = trim($req->requested_score);
                            $scoreSource = 'request_fields';
                        }
                    }

                    // 3) fallback: try to extract from notes/description
                    if (! $scoreDisplay && (!empty($req->notes) || !empty($req->description))) {
                        $candidate = $req->notes ?? $req->description;
                        // pattern matches like "3-2", "25:23", possible multi-set "25-23,25-20"
                        if (preg_match('/\b\d+\s*(?:[:\-])\s*\d+(?:\s*(?:,|\|)\s*\d+\s*(?:[:\-])\s*\d+)*\b/', $candidate, $m)) {
                            $scoreDisplay = $m[0];
                            $scoreSource = 'notes';
                        }
                    }
                @endphp

                <div class="mb-4">
                    <h4 class="font-medium mb-2">Lietotāja iesniegtais rezultāts</h4>

                    @if($scoreDisplay)
                        <div class="text-lg font-semibold">{{ $scoreDisplay }}</div>
                        <div class="text-xs text-gray-500 mt-1">
                            Avots:
                            @if($scoreSource === 'verification')
                                match_score_verifications — {{ optional($verification->created_at)->timezone('Europe/Riga')->format('d.m.Y H:i') ?? '' }}
                            @elseif($scoreSource === 'request_fields')
                                match_requests (requested fields)
                            @else
                                notes/description
                            @endif
                        </div>
                    @else
                        <div class="text-sm text-gray-500">Nav iesniegta rezultāta informācija.</div>
                        @if(!empty($req->notes) || !empty($req->description))
                            <div class="text-sm text-gray-600 mt-2">
                                {!! nl2br(e($req->notes ?? $req->description)) !!}
                            </div>
                        @endif
                    @endif
                </div>

                <h4 class="font-medium mb-2">Spēlētāji</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <div class="text-sm text-gray-500">Mājas komanda</div>
                        <div class="text-sm">
                            @foreach(json_decode($req->home_players ?? '[]', true) as $p)
                                <div>{{ ($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '') }}</div>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Viesu komanda</div>
                        <div class="text-sm">
                            @foreach(json_decode($req->away_players ?? '[]', true) as $p)
                                <div>{{ ($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '') }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if($req->home_logo || $req->away_logo)
                    <div class="mb-4 flex gap-4 items-center">
                        @if($req->home_logo)
                            <div class="text-center">
                                <div class="text-xs text-gray-500 mb-1">Mājas logo</div>
                                <img src="{{ asset('storage/' . $req->home_logo) }}" class="h-24 w-auto rounded border" alt="home logo">
                            </div>
                        @endif
                        @if($req->away_logo)
                            <div class="text-center">
                                <div class="text-xs text-gray-500 mb-1">Viesu logo</div>
                                <img src="{{ asset('storage/' . $req->away_logo) }}" class="h-24 w-auto rounded border" alt="away logo">
                            </div>
                        @endif
                    </div>
                @endif

                <div class="mt-6 flex gap-3">
                    {{-- Approve --}}
                    <form method="POST" action="{{ route('admin.match_requests.accept', $req->id) }}">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                            onclick="return confirm('Apstiprināt šo rezultāta pieprasījumu?');">
                            Apstiprināt
                        </button>
                    </form>

                    {{-- Reject --}}
                    <form method="POST" action="{{ route('admin.match_requests.reject', $req->id) }}">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                            onclick="return confirm('Vai tiešām noraidīt pieprasījumu?');">
                            Noraidīt
                        </button>
                    </form>

                    <a href="{{ route('admin.match_requests.inbox') }}" class="ml-auto text-sm text-gray-600 underline">Atpakaļ uz paskastīti</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
