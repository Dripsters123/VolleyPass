<x-app-layout title="VolleyPass – Pārskats">

    {{-- Welcome header --}}
    <section class="bg-gray-950 relative overflow-hidden">
        <div class="absolute -top-20 -right-20 w-80 h-80 rounded-full bg-orange-500/15 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-20 -left-20 w-80 h-80 rounded-full bg-blue-600/15 blur-3xl pointer-events-none"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex items-center justify-between gap-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-orange-400 mb-1">Mans pārskats</p>
                <h1 class="text-3xl font-extrabold text-white tracking-tight">
                    Sveiks, {{ auth()->user()->name }}!
                </h1>
                <p class="mt-1 text-gray-400">Tavs volejbola kopsavilkums un gaidāmās spēles.</p>
            </div>
            <img src="{{ asset('images/volleyball.png') }}" alt="" width="64" height="64" loading="lazy" class="h-16 w-16 opacity-60 hidden sm:block">
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 overflow-x-hidden">
        <div class="grid lg:grid-cols-3 gap-8 items-start min-w-0">

            {{-- ── Left: Upcoming matches ── --}}
            <section class="lg:col-span-2 space-y-6 min-w-0">

                <div>
                    <div class="flex items-end justify-between mb-5">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-orange-500 mb-1">Spēļu centrs</p>
                            <h2 class="text-xl font-extrabold text-gray-900 tracking-tight">Gaidāmās spēles</h2>
                        </div>
                        <a href="{{ route('local.matches.index') }}"
                           class="flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-700">
                            Visas spēles
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    @if(count($upcomingMatches))
                        <div class="space-y-3">
                            @foreach($upcomingMatches as $m)
                            <a href="{{ route('local.matches.show', $m['id']) }}"
                               class="group flex items-center gap-4 bg-white rounded-2xl border border-gray-200 hover:border-blue-300 hover:shadow-md p-4 transition-all min-w-0 overflow-hidden">
                                <div class="w-1 self-stretch rounded-full bg-gradient-to-b from-orange-400 to-blue-500 shrink-0"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900 truncate group-hover:text-blue-700 transition-colors">
                                        {{ $m['home_team_name'] }} <span class="text-gray-400 font-normal">vs</span> {{ $m['away_team_name'] }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ \Carbon\Carbon::parse($m['start_time'])->translatedFormat('d. M Y, H:i') }}
                                    </p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <span class="text-sm font-bold text-emerald-600">€{{ number_format($m['ticket_price'], 2) }}</span>
                                </div>
                                <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-500 shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center py-12 bg-white rounded-2xl border border-gray-200 text-center">
                            <svg class="w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-gray-500 font-medium">Nav gaidāmu spēļu</p>
                        </div>
                    @endif
                </div>

                {{-- Recent purchases --}}
                <div>
                    <h2 class="text-lg font-extrabold text-gray-900 tracking-tight mb-4">Pēdējie pirkumi</h2>
                    @if($recentPurchases->count())
                        <div class="bg-white rounded-2xl border border-gray-200 divide-y divide-gray-100">
                            @foreach($recentPurchases as $ticket)
                            <div class="flex items-center gap-3 px-5 py-3.5">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                              d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                    </svg>
                                </div>
                                <span class="flex-1 text-sm text-gray-700 truncate">{{ $ticket->event->name ?? 'Biļete' }}</span>
                                <span class="text-sm font-semibold text-gray-900">€{{ $ticket->amount_paid }}</span>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-400 py-4">Nav pirkumu.</p>
                    @endif
                </div>

            </section>

            {{-- ── Right sidebar ── --}}
            <aside class="space-y-5">

                {{-- Quick actions --}}
                <div class="bg-gray-950 rounded-2xl p-5 text-white">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-4">Ātrās darbības</p>
                    <div class="space-y-2">
                        <a href="{{ route('local.matches.index') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/8 hover:bg-white/12 transition-colors text-sm font-medium">
                            <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                      d="M8 21h8M12 17v4M5 3h14l-1 8a6 6 0 01-12 0L5 3z"/>
                            </svg>
                            Skatīt visas spēles
                        </a>
                        <a href="{{ route('match_requests.create') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/8 hover:bg-white/12 transition-colors text-sm font-medium">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v16m8-8H4"/>
                            </svg>
                            Pieteikt maču
                        </a>
                        <a href="{{ route('tickets.index') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/8 hover:bg-white/12 transition-colors text-sm font-medium">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                      d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                            </svg>
                            Manas biļetes
                        </a>
                        <a href="{{ route('wallet.show') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/8 hover:bg-white/12 transition-colors text-sm font-medium">
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18a8 8 0 110-16 8 8 0 010 16zm.75-11.25h-1.5v-1.5h1.5v1.5zm0 7.5h-1.5v-6h1.5v6z"/>
                            </svg>
                            Mans maks
                        </a>
                    </div>
                </div>

                {{-- Recently viewed --}}
                @if(count($recentMatches))
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Nesen skatīts</p>
                    <div class="space-y-2">
                        @foreach($recentMatches as $matchId)
                        <a href="{{ route('local.matches.show', $matchId) }}"
                           class="flex items-center gap-2.5 text-sm text-gray-600 hover:text-blue-600 transition-colors">
                            <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Spēle #{{ $matchId }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

            </aside>
        </div>
    </div>
</x-app-layout>
