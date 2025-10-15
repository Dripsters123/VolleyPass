<x-app-layout>
<div class="container mx-auto p-4 sm:p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl sm:text-3xl font-extrabold">Prognozes</h1>
        <p class="text-sm text-gray-500 hidden sm:block">Prognozējiet un likmējiet ar ⚪ — rediģēt pirms mača sākuma.</p>
    </div>

    {{-- Ziņas --}}
    @if(session('success'))
        <div class="mb-4 rounded-md bg-green-50 border border-green-100 p-3 text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-md bg-red-50 border border-red-100 p-3 text-red-800">{{ session('error') }}</div>
    @endif

    @php
        $upcoming = $upcoming ?? collect();
        $completed = $completed ?? collect();
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- GALVENĀ: Nākamie mači --}}
        <div class="lg:col-span-2 space-y-6">
            <section>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-xl font-semibold">Nākamie mači</h2>
                    <span class="text-sm text-gray-500">{{ $upcoming->total() ?? $upcoming->count() }} mači</span>
                </div>

                <div class="overflow-x-auto lg:overflow-visible">
                    <div class="flex gap-4 pb-4 px-0 lg:grid lg:grid-cols-2 lg:gap-6 lg:pb-0" style="scroll-snap-type: x mandatory;">
                        @forelse($upcoming as $match)
                            @php
                                $userPick = $userPreds[$match->id] ?? null;
                                $staked = $stakes[$match->id] ?? null;
                                $matchStartsAt = \Carbon\Carbon::parse($match->start_time);
                                $matchStarted = now()->gte($match->start_time);
                                $homeLogo = $match->home_logo ? asset('storage/' . $match->home_logo) : null;
                                $awayLogo = $match->away_logo ? asset('storage/' . $match->away_logo) : null;
                                $homeColor = $match->home_color ?? '#1f7af0';
                                $awayColor = $match->away_color ?? '#f04f4f';
                            @endphp

                            <article class="min-w-[84%] sm:min-w-[60%] md:min-w-[48%] lg:min-w-auto bg-white rounded-xl shadow-sm border select-none" style="scroll-snap-align: start;">
                                <div class="p-4 flex flex-col h-full">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 rounded-md flex items-center justify-center overflow-hidden" style="background: {{ $homeColor }}20;">
                                                    @if($homeLogo)
                                                        <img src="{{ $homeLogo }}" alt="home logo" class="h-10 w-10 object-cover">
                                                    @else
                                                        <span class="font-semibold text-white text-sm" style="color: {{ $homeColor }};">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::limit($match->home_team_name, 2, '')) }}</span>
                                                    @endif
                                                </div>
                                                <div class="text-sm font-medium">{{ $match->home_team_name }}</div>
                                            </div>

                                            <div class="text-xs text-gray-400 mx-2">vs</div>

                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 rounded-md flex items-center justify-center overflow-hidden" style="background: {{ $awayColor }}20;">
                                                    @if($awayLogo)
                                                        <img src="{{ $awayLogo }}" alt="away logo" class="h-10 w-10 object-cover">
                                                    @else
                                                        <span class="font-semibold text-white text-sm" style="color: {{ $awayColor }};">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::limit($match->away_team_name, 2, '')) }}</span>
                                                    @endif
                                                </div>
                                                <div class="text-sm font-medium">{{ $match->away_team_name }}</div>
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <div class="text-sm text-gray-500">{{ $matchStartsAt->format('d M Y') }}</div>
                                            <div class="text-sm font-semibold">{{ $matchStartsAt->format('H:i') }}</div>
                                        </div>
                                    </div>

                                    <div class="my-3 border-t"></div>

                                    <form method="POST" action="{{ route('predictions.store') }}" x-data="{ pick: '{{ $userPick ?? '' }}', stake: '{{ $staked ?? '' }}', submitting: false }" @submit="submitting = true; $event.target.querySelector('button[type=submit]').disabled = true;" class="mt-2 flex flex-col gap-3">
                                        @csrf
                                        <input type="hidden" name="match_id" value="{{ $match->id }}">

                                        <div class="grid grid-cols-2 gap-2">
                                            <label :class="pick==='home' ? 'ring-2 ring-offset-2 ring-blue-400' : ''" class="flex items-center gap-3 p-2 rounded-md border hover:bg-gray-50 cursor-pointer">
                                                <input type="radio" name="prediction" value="home" x-model="pick" class="hidden" {{ $matchStarted ? 'disabled' : '' }}>
                                                <div class="flex-1 text-sm">
                                                    <div class="font-medium">{{ $match->home_team_name }}</div>
                                                    <div class="text-xs text-gray-500">Mājas</div>
                                                </div>
                                                <div class="text-xs text-gray-500">⚪</div>
                                            </label>

                                            <label :class="pick==='away' ? 'ring-2 ring-offset-2 ring-blue-400' : ''" class="flex items-center gap-3 p-2 rounded-md border hover:bg-gray-50 cursor-pointer">
                                                <input type="radio" name="prediction" value="away" x-model="pick" class="hidden" {{ $matchStarted ? 'disabled' : '' }}>
                                                <div class="flex-1 text-sm">
                                                    <div class="font-medium">{{ $match->away_team_name }}</div>
                                                    <div class="text-xs text-gray-500">Izbraukums</div>
                                                </div>
                                                <div class="text-xs text-gray-500">⚪</div>
                                            </label>
                                        </div>

                                        <div class="flex gap-2 items-center">
                                            <input type="number" step="0.01" name="staked_coins" x-model="stake" placeholder="Likme (⚪)" class="flex-1 border rounded-md px-3 py-2 text-sm" {{ $matchStarted ? 'disabled' : '' }}>
                                            <div class="text-sm text-gray-500">⚪</div>
                                        </div>

                                        <div class="flex items-center justify-between gap-3">
                                            <button type="submit" :disabled="submitting || pick==='' || {{ $matchStarted ? 'true' : 'false' }}" class="px-4 py-2 rounded-md text-sm font-semibold bg-blue-600 text-white disabled:opacity-50 disabled:cursor-not-allowed">
                                                <span x-text="( '{{ $userPick ? 'Atjaunināt' : 'Iesniegt' }}' )"></span>
                                            </button>

                                            <div class="text-sm text-right">
                                                @if($userPick)
                                                    <div class="text-gray-700">Jūsu izvēle: <strong>{{ ucfirst($userPick) }}</strong></div>
                                                    @if($staked)
                                                        <div class="text-gray-500">Likme: <strong>{{ $staked }}</strong> ⚪</div>
                                                    @endif
                                                @else
                                                    <div class="text-gray-500">Vēl nav prognozes</div>
                                                @endif
                                            </div>
                                        </div>

                                        @if($matchStarted)
                                            <div class="text-xs text-red-600">Spēle jau sākusies — likmēšana aizliegta.</div>
                                        @endif
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="text-gray-500">Nav nākamo maču.</div>
                        @endforelse
                    </div>
                </div>

                {{-- Paginācija --}}
                <div class="mt-4">
                    {{ $upcoming->links() }}
                </div>
            </section>
        </div>

        {{-- SĀNU PANELIS: Manas prognozes + Pabeigtie mači --}}
        <aside class="lg:col-span-1">
            <div class="sticky top-6 space-y-6">
                {{-- Manas prognozes --}}
                <div class="bg-white border rounded-lg p-4 shadow-sm">
                    <h3 class="font-semibold mb-3">Manas prognozes</h3>

                    @if(isset($recentPredictions) && $recentPredictions->count() > 0)
                        <ul class="space-y-3 mb-4">
                            @foreach($recentPredictions as $p)
                                @php $m = $recentMatches[$p->match_id] ?? null; @endphp
                                <li class="text-sm">
                                    @if($m)
                                        <div class="font-medium">{{ $m->home_team_name }} vs {{ $m->away_team_name }}</div>
                                        <div class="text-gray-600">Izvēle: <strong>{{ ucfirst($p->prediction) }}</strong>
                                            @if($p->staked_coins) — Likme: {{ $p->staked_coins }} ⚪ @endif
                                        </div>
                                    @else
                                        <div class="text-gray-600">Prognoze mačam #{{ $p->match_id }}</div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-gray-500 mb-4">Jums nav prognožu.</div>
                    @endif

                    <a href="{{ route('predictions.my') }}" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">Skatīt visas manas prognozes</a>
                </div>

                {{-- Pabeigtie mači (sidebar) --}}
                <div class="bg-white border rounded-lg p-4 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold">Pabeigtie mači</h4>
                        <span class="text-sm text-gray-500">{{ $completed->count() }}</span>
                    </div>

                    <div class="space-y-3 max-h-[60vh] overflow-auto pr-2">
                        @forelse($completed as $match)
                            @php
                                $userPick = $userPreds[$match->id] ?? null;
                                $staked = $stakes[$match->id] ?? 0;
                                $reward = $rewards[$match->id] ?? null;
                                $homeScore = intval($match->home_score ?? 0);
                                $awayScore = intval($match->away_score ?? 0);
                                $winner = $homeScore > $awayScore ? 'home' : 'away';
                            @endphp

                            <div class="border rounded-md p-3 bg-gray-50">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-medium text-sm">
                                            {{ $match->home_team_name }} <span class="text-gray-600 text-sm">{{ $homeScore }}</span>
                                            <span class="mx-1 text-gray-400">-</span>
                                            <span class="text-gray-600 text-sm">{{ $awayScore }}</span> {{ $match->away_team_name }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($match->start_time)->format('d M Y, H:i') }}</div>

                                        @if($userPick)
                                            <div class="mt-2 text-sm">
                                                Izvēle: <strong>{{ ucfirst($userPick) }}</strong>
                                                <span class="ml-2 px-2 py-0.5 rounded-full text-xs {{ ($winner === $userPick && $reward > 0) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ ($winner === $userPick && $reward > 0) ? 'Uzvara' : 'Zaudējums' }}
                                                </span>
                                            </div>

                                            <div class="text-sm text-gray-600 mt-1">Likme: <strong>{{ $staked }}</strong> ⚪
                                                @if($reward !== null)
                                                    — Balva: <strong>{{ $reward }}</strong> ⚪
                                                @endif
                                            </div>
                                        @else
                                            <div class="mt-2 text-sm text-gray-500">Jūs neprognozējāt šo maču.</div>
                                        @endif
                                    </div>

                                    <div class="text-right text-xs">
                                        <div class="text-gray-600">Stāvoklis</div>
                                        <div class="mt-1 font-medium">{{ ucfirst($match->match_state ?? $match->status_type ?? 'Pabeigts') }}</div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-gray-500">Nav pabeigto maču.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
</x-app-layout>
