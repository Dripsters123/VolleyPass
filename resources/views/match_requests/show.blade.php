<x-app-layout>
<div class="max-w-4xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Mača pieprasījuma informācija</h1>
            <p class="text-sm text-gray-500 mt-0.5">#{{ $matchRequest->id }} &mdash; iesniegts {{ $matchRequest->created_at?->timezone('Europe/Riga')->format('d.m.Y H:i') }}</p>
        </div>
        @php
            $statusLabels = ['pending'=>'Gaida','reviewing'=>'Tiek izskatīts','accepted'=>'Apstiprināts','rejected'=>'Noraidīts','appealed'=>'Apelācija nosūtīta'];
            $statusColors = [
                'pending'   => 'bg-yellow-100 text-yellow-700',
                'reviewing' => 'bg-blue-100 text-blue-700',
                'accepted'  => 'bg-green-100 text-green-700',
                'rejected'  => 'bg-red-100 text-red-700',
                'appealed'  => 'bg-purple-100 text-purple-700',
            ];
        @endphp
        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold {{ $statusColors[$matchRequest->status] ?? 'bg-gray-100 text-gray-700' }}">
            {{ $statusLabels[$matchRequest->status] ?? ucfirst($matchRequest->status ?? 'pending') }}
        </span>
    </div>

    {{-- Rejection reason banner --}}
    @if($matchRequest->status === 'rejected' && $matchRequest->rejection_reason)
        <div class="mb-6 bg-red-50 border border-red-200 rounded-2xl p-5">
            <div class="flex items-start gap-3">
                <span class="text-2xl flex-shrink-0">❌</span>
                <div>
                    <div class="font-semibold text-red-700 mb-1">Pieprasījums noraidīts</div>
                    <p class="text-sm text-red-600">{{ $matchRequest->rejection_reason }}</p>
                </div>
            </div>
        </div>
    @elseif($matchRequest->status === 'reviewing')
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-2xl p-5 flex items-center gap-3">
            <span class="text-2xl">👁</span>
            <p class="text-sm text-blue-700 font-medium">Jūsu pieprasījums tiek izskatīts. Drīzumā saņemsiet atbildi.</p>
        </div>
    @elseif($matchRequest->status === 'appealed')
        <div class="mb-6 bg-purple-50 border border-purple-200 rounded-2xl p-5 flex items-center gap-3">
            <span class="text-2xl">📢</span>
            <p class="text-sm text-purple-700 font-medium">Jūsu apelācija nosūtīta. Gaidiet administratora lēmumu.</p>
        </div>
    @endif

    {{-- Match meta cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Sākums</div>
            <div class="text-sm font-semibold text-gray-800">{{ isset($matchRequest->start_time) ? \Carbon\Carbon::parse($matchRequest->start_time)->format('d.m.Y H:i') : '—' }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Beigas</div>
            <div class="text-sm font-semibold text-gray-800">{{ isset($matchRequest->end_time) ? \Carbon\Carbon::parse($matchRequest->end_time)->format('d.m.Y H:i') : '—' }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Formāts</div>
            <div class="text-sm font-semibold text-gray-800">{{ $matchRequest->players_per_team ?? '?' }} × {{ $matchRequest->players_per_team ?? '?' }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Arena</div>
            <div class="text-sm font-semibold text-gray-800 truncate">{{ $matchRequest->arena_name ?? '—' }}</div>
        </div>
    </div>

    {{-- Visual Volleyball Court --}}
    @php
        $homePlayers = is_array($matchRequest->home_players) ? $matchRequest->home_players : (json_decode($matchRequest->home_players ?? '[]', true) ?: []);
        $awayPlayers = is_array($matchRequest->away_players) ? $matchRequest->away_players : (json_decode($matchRequest->away_players ?? '[]', true) ?: []);
        $n = (int)($matchRequest->players_per_team ?? max(count($homePlayers), count($awayPlayers), 2));

        // Standard volleyball positions by count
        $positions = [
            2 => [[25,65],[75,65]],
            4 => [[20,40],[80,40],[20,80],[80,80]],
            6 => [[16,35],[50,35],[84,35],[16,75],[50,75],[84,75]],
        ];
        $homePos = $positions[$n] ?? $positions[6];
        $awayPos = array_map(fn($p) => [100-$p[0], 100-$p[1]], $homePos);
    @endphp

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-8">
        <h2 class="text-base font-semibold text-gray-700 mb-4">Volejbola laukums</h2>

        {{-- Court SVG --}}
        <div class="w-full overflow-x-auto">
            <svg viewBox="0 0 100 100" class="w-full max-w-2xl mx-auto block rounded-xl" style="aspect-ratio:2/1; max-height:340px;"
                 xmlns="http://www.w3.org/2000/svg">

                {{-- Court background --}}
                <defs>
                    <linearGradient id="courtGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" style="stop-color:#f59e0b;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#d97706;stop-opacity:1" />
                    </linearGradient>
                </defs>
                <rect width="100" height="100" fill="url(#courtGrad)" rx="3"/>

                {{-- Court lines --}}
                {{-- Outer boundary --}}
                <rect x="3" y="8" width="94" height="84" fill="none" stroke="white" stroke-width="0.6" opacity="0.9"/>

                {{-- Center line (net) --}}
                <line x1="50" y1="8" x2="50" y2="92" stroke="white" stroke-width="1.2" opacity="0.95"/>

                {{-- Attack lines (3m lines) --}}
                <line x1="3" y1="8" x2="3" y2="92" stroke="white" stroke-width="0.3" opacity="0.5"/>
                <line x1="29" y1="8" x2="29" y2="92" stroke="white" stroke-width="0.5" opacity="0.7"/>
                <line x1="71" y1="8" x2="71" y2="92" stroke="white" stroke-width="0.5" opacity="0.7"/>
                <line x1="97" y1="8" x2="97" y2="92" stroke="white" stroke-width="0.3" opacity="0.5"/>

                {{-- Service zone markers --}}
                <line x1="3" y1="92" x2="8" y2="92" stroke="white" stroke-width="0.6"/>
                <line x1="97" y1="92" x2="92" y2="92" stroke="white" stroke-width="0.6"/>

                {{-- Net post indicators --}}
                <circle cx="50" cy="7" r="1.2" fill="white" opacity="0.8"/>
                <circle cx="50" cy="93" r="1.2" fill="white" opacity="0.8"/>

                {{-- Team labels --}}
                <text x="26" y="5.5" text-anchor="middle" font-size="3" fill="white" font-weight="bold" opacity="0.9">
                    {{ \Illuminate\Support\Str::limit($matchRequest->home_team ?? 'Mājas', 14) }}
                </text>
                <text x="74" y="5.5" text-anchor="middle" font-size="3" fill="white" font-weight="bold" opacity="0.9">
                    {{ \Illuminate\Support\Str::limit($matchRequest->away_team ?? 'Viesi', 14) }}
                </text>

                {{-- Home players (left side) --}}
                @foreach($homePlayers as $i => $player)
                    @php
                        $pos = $homePos[$i] ?? [$homePos[0][0], $homePos[0][1]];
                        $name = is_array($player) ? (($player['first_name'] ?? '') . ' ' . ($player['last_name'] ?? '')) : $player;
                        $initials = collect(explode(' ', trim($name)))->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
                        $initials = substr($initials, 0, 2);
                    @endphp
                    <g transform="translate({{ $pos[0] }}, {{ $pos[1] }})">
                        <circle r="5.5" fill="#1e40af" stroke="white" stroke-width="0.7"/>
                        <text text-anchor="middle" dominant-baseline="central" font-size="3.2" fill="white" font-weight="bold">{{ $initials ?: '?' }}</text>
                        <text text-anchor="middle" y="8" font-size="2.2" fill="white" opacity="0.85">{{ \Illuminate\Support\Str::limit(trim($name), 10) }}</text>
                    </g>
                @endforeach

                {{-- Away players (right side) --}}
                @foreach($awayPlayers as $i => $player)
                    @php
                        $pos = $awayPos[$i] ?? [$awayPos[0][0], $awayPos[0][1]];
                        $name = is_array($player) ? (($player['first_name'] ?? '') . ' ' . ($player['last_name'] ?? '')) : $player;
                        $initials = collect(explode(' ', trim($name)))->map(fn($w) => strtoupper($w[0] ?? ''))->join('');
                        $initials = substr($initials, 0, 2);
                    @endphp
                    <g transform="translate({{ $pos[0] }}, {{ $pos[1] }})">
                        <circle r="5.5" fill="#991b1b" stroke="white" stroke-width="0.7"/>
                        <text text-anchor="middle" dominant-baseline="central" font-size="3.2" fill="white" font-weight="bold">{{ $initials ?: '?' }}</text>
                        <text text-anchor="middle" y="8" font-size="2.2" fill="white" opacity="0.85">{{ \Illuminate\Support\Str::limit(trim($name), 10) }}</text>
                    </g>
                @endforeach

                {{-- Net label --}}
                <text x="50" y="97.5" text-anchor="middle" font-size="2.2" fill="white" opacity="0.7">TĪKLS</text>
            </svg>
        </div>

        {{-- Legend --}}
        <div class="flex justify-center gap-6 mt-3 text-xs text-gray-500">
            <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-full bg-blue-800"></span>{{ $matchRequest->home_team ?? 'Mājas komanda' }}</span>
            <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-full bg-red-800"></span>{{ $matchRequest->away_team ?? 'Viesu komanda' }}</span>
        </div>
    </div>

    {{-- Player rosters --}}
    <div class="grid md:grid-cols-2 gap-6 mb-8">
        {{-- Home team --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center gap-3 mb-4">
                @if($matchRequest->home_logo)
                    <img src="{{ asset('storage/'.$matchRequest->home_logo) }}" class="w-10 h-10 rounded-full object-cover border" alt="">
                @else
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-blue-700 font-bold text-sm">{{ strtoupper(substr($matchRequest->home_team ?? 'H', 0, 1)) }}</span>
                    </div>
                @endif
                <div>
                    <h3 class="font-semibold text-gray-900">{{ $matchRequest->home_team ?? 'Mājas komanda' }}</h3>
                    @if($matchRequest->home_coach)
                        <p class="text-xs text-gray-400">Treneris: {{ $matchRequest->home_coach }}</p>
                    @endif
                </div>
            </div>
            <ul class="space-y-1.5">
                @foreach($homePlayers as $i => $p)
                    <li class="flex items-center gap-2.5 text-sm text-gray-700">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center">{{ $i+1 }}</span>
                        @if(is_array($p))
                            {{ ($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '') }}
                        @else
                            {{ $p }}
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Away team --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center gap-3 mb-4">
                @if($matchRequest->away_logo)
                    <img src="{{ asset('storage/'.$matchRequest->away_logo) }}" class="w-10 h-10 rounded-full object-cover border" alt="">
                @else
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <span class="text-red-700 font-bold text-sm">{{ strtoupper(substr($matchRequest->away_team ?? 'A', 0, 1)) }}</span>
                    </div>
                @endif
                <div>
                    <h3 class="font-semibold text-gray-900">{{ $matchRequest->away_team ?? 'Viesu komanda' }}</h3>
                    @if($matchRequest->away_coach)
                        <p class="text-xs text-gray-400">Treneris: {{ $matchRequest->away_coach }}</p>
                    @endif
                </div>
            </div>
            <ul class="space-y-1.5">
                @foreach($awayPlayers as $i => $p)
                    <li class="flex items-center gap-2.5 text-sm text-gray-700">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-red-100 text-red-700 font-bold text-xs flex items-center justify-center">{{ $i+1 }}</span>
                        @if(is_array($p))
                            {{ ($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '') }}
                        @else
                            {{ $p }}
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Judges --}}
    @if(!empty($matchRequest->judges))
        @php
            $judges = is_array($matchRequest->judges) ? $matchRequest->judges : (json_decode($matchRequest->judges ?? '[]', true) ?: []);
        @endphp
        @if(count($judges) > 0)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-8">
            <h3 class="font-semibold text-gray-700 mb-3">Tiesneši</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($judges as $j)
                    <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">{{ $j }}</span>
                @endforeach
            </div>
        </div>
        @endif
    @endif

    {{-- Actions --}}
    <div class="flex justify-between items-center flex-wrap gap-3">
        <a href="{{ route('match_requests.my') }}"
           class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition">
            ← Atpakaļ
        </a>
        @if($matchRequest->status === 'pending')
            <div class="flex gap-3">
                <a href="{{ route('match_requests.edit', $matchRequest->id) }}"
                   class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-sm font-medium transition">
                    Rediģēt
                </a>
                <form method="POST" action="{{ route('match_requests.cancel', $matchRequest->id) }}"
                      onsubmit="return confirm('Vai tiešām vēlaties atcelt šo pieprasījumu?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition">
                        Atcelt
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- Appeal form (shown only for rejected requests) --}}
    @if($matchRequest->status === 'rejected')
        <div class="mt-6 bg-orange-50 border border-orange-200 rounded-2xl p-6" x-data="{ open: false }">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-orange-800">Iesniegt apelāciju</h3>
                    <p class="text-sm text-orange-600 mt-0.5">Paskaidrojiet, kāpēc jūsu pieprasījums ir pamatots.</p>
                </div>
                <button @click="open = !open"
                        class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition">
                    📢 Apelēt
                </button>
            </div>
            <div x-show="open" x-transition class="mt-4">
                <form method="POST" action="{{ route('match_requests.appeal', $matchRequest->id) }}">
                    @csrf
                    <textarea name="appeal_message" rows="4" required
                        class="w-full rounded-xl border-orange-300 text-sm focus:ring-orange-500 focus:border-orange-500"
                        placeholder="Piemēram: Esmu pievienojis visas nepieciešamās spēlētāju detaļas…">{{ old('appeal_message') }}</textarea>
                    @error('appeal_message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    <div class="mt-3 flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition">
                            Iesniegt apelāciju
                        </button>
                        <button type="button" @click="open = false"
                                class="px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                            Atcelt
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Delete rejected request --}}
        <div class="mt-3 flex justify-end">
            <form method="POST" action="{{ route('match_requests.destroy', $matchRequest->id) }}"
                  onsubmit="return confirm('Dzēst šo pieprasījumu? Šī darbība ir neatgriezeniska.');">
                @csrf @method('DELETE')
                <button type="submit"
                        class="px-4 py-2 text-xs text-gray-400 hover:text-red-600 border border-gray-200 hover:border-red-300 rounded-xl transition">
                    🗑 Dzēst pieprasījumu
                </button>
            </form>
        </div>
    @endif
</div>
</x-app-layout>