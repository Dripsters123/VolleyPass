{{-- resources/views/match_requests/view.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Mača pieprasījums — #{{ $request->id }}
                </h2>
                <div class="text-sm text-gray-500 mt-1">
                    Iesniedzējs: {{ optional($request->user)->name ?? auth()->user()->name }}
                    @if(optional($request->user)->email)
                        ({{ optional($request->user)->email }})
                    @endif
                </div>
            </div>

            <div class="text-right">
                <div class="text-sm">
                    <span class="inline-block px-3 py-1 rounded
                        {{ ($request->status ?? '') === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ ($request->status ?? '') === 'accepted' ? 'bg-green-100 text-green-800' : '' }}
                        {{ ($request->status ?? '') === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ ucfirst($request->status ?? 'pending') }}
                    </span>
                </div>
                <div class="text-xs text-gray-500 mt-1">
                    Iesniegts: {{ optional($request->created_at)->timezone('Europe/Riga')->format('d.m.Y H:i') ?? '—' }}
                </div>
            </div>
        </div>
    </x-slot>

    @php
        // avoid colliding with request() helper — use $mr as model variable
        $mr = $request;

        // Try to find a verification row (if you have that model/table)
        $verification = null;
        try {
            if (!empty($mr->match_id)) {
                $verification = \App\Models\MatchScoreVerification::where('match_id', $mr->match_id)
                    ->where('user_id', $mr->user_id)
                    ->latest()
                    ->first();
            } else {
                // best-effort fallback by team+date (non-exact)
                if (!empty($mr->home_team) && !empty($mr->away_team) && !empty($mr->start_time)) {
                    $m = \App\Models\VolleyballMatch::where('home_team_name', $mr->home_team)
                        ->where('away_team_name', $mr->away_team)
                        ->whereDate('start_time', \Carbon\Carbon::parse($mr->start_time)->toDateString())
                        ->first();
                    if ($m) {
                        $verification = \App\Models\MatchScoreVerification::where('match_id', $m->id)
                            ->where('user_id', $mr->user_id)
                            ->latest()
                            ->first();
                    }
                }
            }
        } catch (\Throwable $e) {
            $verification = null;
        }

        // derive a score display (prefer verification then requested_* then notes)
        $scoreDisplay = null;
        $scoreSource = null;
        if (isset($verification) && $verification) {
            if (!is_null($verification->home_score) || !is_null($verification->away_score)) {
                $scoreDisplay = trim(($verification->home_score ?? '?') . ' - ' . ($verification->away_score ?? '?'));
                $scoreSource = 'verification';
            }
        }

        if (! $scoreDisplay) {
            if (!empty($mr->requested_home_score) || !empty($mr->requested_away_score)) {
                $scoreDisplay = (($mr->requested_home_score ?? '?') . ' - ' . ($mr->requested_away_score ?? '?'));
                $scoreSource = 'request_fields';
            } elseif (!empty($mr->requested_score)) {
                $scoreDisplay = trim($mr->requested_score);
                $scoreSource = 'request_fields';
            }
        }

        if (! $scoreDisplay && (!empty($mr->notes) || !empty($mr->description))) {
            $candidate = $mr->notes ?? $mr->description;
            if (preg_match('/\b\d+\s*(?:[:\-])\s*\d+(?:\s*(?:,|\|)\s*\d+\s*(?:[:\-])\s*\d+)*\b/', $candidate, $m)) {
                $scoreDisplay = $m[0];
                $scoreSource = 'notes';
            }
        }
    @endphp

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium mb-4">Mača pamatinformācija</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="text-sm text-gray-500">Mājas komanda</div>
                        <div class="font-semibold">{{ $mr->home_team ?? ($mr->home_team_name ?? '—') }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Viesu komanda</div>
                        <div class="font-semibold">{{ $mr->away_team ?? ($mr->away_team_name ?? '—') }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Sākums</div>
                        <div>
                            @if(isset($mr->start_time))
                                {{ \Carbon\Carbon::parse($mr->start_time)->timezone('Europe/Riga')->format('d.m.Y H:i') }}
                            @else
                                —
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Beigas</div>
                        <div>
                            @if(isset($mr->end_time))
                                {{ \Carbon\Carbon::parse($mr->end_time)->timezone('Europe/Riga')->format('d.m.Y H:i') }}
                            @else
                                —
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <h4 class="font-medium">Vieta</h4>
                    <div class="text-sm text-gray-700">{{ $mr->location ?? '—' }}</div>
                </div>
            </div>

            {{-- Requested score --}}
            <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium mb-3">Iesniegtais rezultāts</h3>

                @if($scoreDisplay)
                    <div class="text-2xl font-semibold">{{ $scoreDisplay }}</div>
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
                    @if(!empty($mr->notes) || !empty($mr->description))
                        <div class="text-sm text-gray-600 mt-2">{!! nl2br(e($mr->notes ?? $mr->description)) !!}</div>
                    @endif
                @endif
            </div>

            {{-- Players --}}
            <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium mb-3">Spēlētāji</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="text-sm text-gray-500">Mājas komanda</div>
                        <div class="text-sm">
                            @php $homePlayers = is_array($mr->home_players) ? $mr->home_players : (json_decode($mr->home_players ?? '[]', true) ?: []); @endphp
                            @forelse($homePlayers as $p)
                                <div>{{ ($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '') }}</div>
                            @empty
                                <div class="text-sm text-gray-500">Nav datu</div>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Viesu komanda</div>
                        <div class="text-sm">
                            @php $awayPlayers = is_array($mr->away_players) ? $mr->away_players : (json_decode($mr->away_players ?? '[]', true) ?: []); @endphp
                            @forelse($awayPlayers as $p)
                                <div>{{ ($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '') }}</div>
                            @empty
                                <div class="text-sm text-gray-500">Nav datu</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Coaches --}}
            @if($mr->home_coach || $mr->away_coach)
                <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium mb-3">Treneri</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-gray-500">Mājas treneris</div>
                            <div class="font-medium">{{ $mr->home_coach ?: '—' }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Viesu treneris</div>
                            <div class="font-medium">{{ $mr->away_coach ?: '—' }}</div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Judges --}}
            @php
                $judgesList = is_array($mr->judges) ? $mr->judges : (json_decode($mr->judges ?? '[]', true) ?: []);
            @endphp
            @if(!empty($judgesList))
                <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium mb-3">Tiesneši</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($judgesList as $judge)
                            <span class="inline-block px-3 py-1 bg-gray-100 rounded-full text-sm text-gray-700">{{ $judge }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Arena --}}
            @if(!empty($mr->arena_name))
                <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium mb-3">Arēna</h3>
                    <div class="text-sm text-gray-700 font-medium">{{ $mr->arena_name }}</div>
                    @if($mr->arena_width && $mr->arena_height)
                        <div class="text-xs text-gray-500 mt-1">Izmērs: {{ $mr->arena_width }} × {{ $mr->arena_height }} px</div>
                    @endif

                    {{-- Arena layout canvas --}}
                    @php
                        $arenaElements = is_array($mr->arena_elements) ? $mr->arena_elements : (json_decode($mr->arena_elements ?? '[]', true) ?: []);
                    @endphp
                    @if(!empty($arenaElements))
                        <div class="mt-4 overflow-auto rounded-xl border border-gray-100 bg-gray-50">
                            <canvas id="arenaPreview" class="block mx-auto" style="max-width:100%;"></canvas>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-4 text-xs text-gray-500 justify-center">
                            <span class="flex items-center gap-1.5"><span class="inline-block w-4 h-4 rounded bg-amber-400 border border-amber-600"></span>Laukums</span>
                            <span class="flex items-center gap-1.5"><span class="inline-block w-4 h-4 rounded bg-sky-600 border border-sky-700"></span>Sēdvieta</span>
                        </div>
                        <script>
                        (function(){
                            var elements = @json($arenaElements);
                            var srcW = {{ (int)($mr->arena_width ?? 1000) }};
                            var srcH = {{ (int)($mr->arena_height ?? 700) }};
                            var canvas = document.getElementById('arenaPreview');
                            var maxW = Math.min(860, canvas.parentElement.clientWidth - 32);
                            var scale = Math.min(maxW / srcW, 520 / srcH, 1);
                            var cw = Math.round(srcW * scale);
                            var ch = Math.round(srcH * scale);
                            canvas.width = cw; canvas.height = ch;
                            canvas.style.width = cw + 'px'; canvas.style.height = ch + 'px';
                            var ctx = canvas.getContext('2d');
                            ctx.fillStyle = '#f8fafc'; ctx.fillRect(0, 0, cw, ch);
                            ctx.strokeStyle = '#e2e8f0'; ctx.lineWidth = 0.5;
                            var gs = Math.round(50 * scale);
                            for (var x = 0; x < cw; x += gs) { ctx.beginPath(); ctx.moveTo(x,0); ctx.lineTo(x,ch); ctx.stroke(); }
                            for (var y = 0; y < ch; y += gs) { ctx.beginPath(); ctx.moveTo(0,y); ctx.lineTo(cw,y); ctx.stroke(); }
                            function roundRect(ctx, x, y, w, h, r) {
                                ctx.beginPath(); ctx.moveTo(x+r, y);
                                ctx.lineTo(x+w-r, y); ctx.quadraticCurveTo(x+w, y, x+w, y+r);
                                ctx.lineTo(x+w, y+h-r); ctx.quadraticCurveTo(x+w, y+h, x+w-r, y+h);
                                ctx.lineTo(x+r, y+h); ctx.quadraticCurveTo(x, y+h, x, y+h-r);
                                ctx.lineTo(x, y+r); ctx.quadraticCurveTo(x, y, x+r, y); ctx.closePath();
                            }
                            elements.forEach(function(el) {
                                var ex = Math.round(el.x*scale), ey = Math.round(el.y*scale);
                                var ew = Math.round(el.width*scale), eh = Math.round(el.height*scale);
                                if (el.type === 'court') {
                                    ctx.fillStyle = '#fbbf24'; ctx.strokeStyle = '#b45309'; ctx.lineWidth = 1.5;
                                    roundRect(ctx, ex, ey, ew, eh, 8); ctx.fill(); ctx.stroke();
                                    ctx.fillStyle = '#92400e'; ctx.font = 'bold ' + Math.max(10, Math.round(14*scale)) + 'px sans-serif';
                                    ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
                                    ctx.fillText(el.label || 'Laukums', ex+ew/2, ey+eh/2);
                                } else {
                                    ctx.fillStyle = '#0284c7'; ctx.strokeStyle = '#0369a1'; ctx.lineWidth = 1;
                                    roundRect(ctx, ex, ey, ew, eh, 6); ctx.fill(); ctx.stroke();
                                    ctx.fillStyle = '#fff'; ctx.font = 'bold ' + Math.max(7, Math.round(11*scale)) + 'px sans-serif';
                                    ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
                                    ctx.fillText(el.number || el.label || '', ex+ew/2, ey+eh/2);
                                }
                            });
                        })();
                        </script>
                    @endif
                </div>
            @endif

            {{-- Logos --}}
            @if($mr->home_logo || $mr->away_logo)
                <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium mb-3">Logo</h3>
                    <div class="flex gap-6 items-center">
                        @if($mr->home_logo)
                            <div class="text-center">
                                <div class="text-xs text-gray-500 mb-1">Mājas logo</div>
                                <img src="{{ asset('storage/' . $mr->home_logo) }}" class="h-24 w-auto rounded border" alt="home logo">
                            </div>
                        @endif
                        @if($mr->away_logo)
                            <div class="text-center">
                                <div class="text-xs text-gray-500 mb-1">Viesu logo</div>
                                <img src="{{ asset('storage/' . $mr->away_logo) }}" class="h-24 w-auto rounded border" alt="away logo">
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            <div class="flex gap-3 items-center">
                <a href="{{ route('match_requests.my') }}" class="text-sm text-gray-600 underline">Atpakaļ uz pieprasījumiem</a>

                @if(auth()->check() && auth()->id() === $mr->user_id && ($mr->status ?? '') === 'pending')
                    <a href="{{ route('match_requests.edit', $mr->id) }}" class="ml-auto inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Rediģēt</a>

                    <form method="POST" action="{{ route('match_requests.cancel', $mr->id) }}" onsubmit="return confirm('Vai tiešām atcelt šo pieprasījumu?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Atcelt</button>
                    </form>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
