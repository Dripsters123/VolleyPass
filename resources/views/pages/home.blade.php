<x-app-layout title="VolleyPass – Sākumlapa">

    {{-- ═══ HERO ═══ --}}
    <section class="relative bg-gray-950 overflow-hidden">
        {{-- decorative gradient blobs --}}
        <div class="absolute -top-32 -left-32 w-[500px] h-[500px] rounded-full bg-orange-500/20 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-[500px] h-[500px] rounded-full bg-blue-600/20 blur-3xl pointer-events-none"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32 flex flex-col md:flex-row items-center gap-12">
            {{-- Text --}}
            <div class="flex-1 text-center md:text-left">
                <span class="inline-block mb-4 px-3 py-1 rounded-full text-xs font-semibold tracking-widest uppercase bg-orange-500/15 text-orange-400 border border-orange-500/30">
                    Latvijas volejbols
                </span>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight tracking-tight">
                    Tavs volejbola<br>
                    <span class="bg-gradient-to-r from-orange-400 to-blue-500 bg-clip-text text-transparent">centrs.</span>
                </h1>
                <p class="mt-5 text-lg text-gray-400 max-w-lg mx-auto md:mx-0">
                    Atrod spēles, iegādājies biļetes un seko savam komandas ceļam – viss vienā vietā.
                </p>
                <div class="mt-8 flex flex-wrap gap-3 justify-center md:justify-start">
                    <a href="{{ route('local.matches.index') }}"
                       class="px-6 py-3 rounded-xl font-semibold bg-gradient-to-r from-orange-500 to-blue-600 text-white hover:opacity-90 transition-opacity shadow-lg shadow-orange-500/20">
                        Skatīt visas spēles
                    </a>
                    @guest
                    <a href="{{ route('register') }}"
                       class="px-6 py-3 rounded-xl font-semibold border border-white/20 text-gray-300 hover:text-white hover:border-white/40 transition-colors">
                        Reģistrēties
                    </a>
                    @endguest
                </div>
            </div>

            {{-- Volleyball graphic --}}
            <div class="flex-shrink-0 flex items-center justify-center">
                <div class="relative w-52 h-52 md:w-64 md:h-64">
                    <div class="absolute inset-0 rounded-full bg-gradient-to-br from-orange-400/30 to-blue-600/30 blur-2xl"></div>
                    <img src="{{ asset('images/volleyball.png') }}" alt="Volleyball"
                         class="relative w-full h-full object-contain drop-shadow-2xl">
                </div>
            </div>
        </div>
    </section>

    {{-- ═══ STATS BAR ═══ --}}
    <section class="bg-gray-900 border-y border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-wrap justify-center gap-8 md:gap-16 text-center">
            <div>
                <p class="text-2xl font-extrabold text-white">{{ count($matches) }}+</p>
                <p class="text-xs text-gray-400 mt-0.5 uppercase tracking-wide">Gaidāmās spēles</p>
            </div>
            <div>
                <p class="text-2xl font-extrabold text-white">100%</p>
                <p class="text-xs text-gray-400 mt-0.5 uppercase tracking-wide">Drošs maksājums</p>
            </div>
            <div>
                <p class="text-2xl font-extrabold text-white">E-biļete</p>
                <p class="text-xs text-gray-400 mt-0.5 uppercase tracking-wide">Tūlītēja piegāde</p>
            </div>
        </div>
    </section>

    {{-- ═══ MATCH CENTER ═══ --}}
    <section class="bg-gray-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Section header --}}
            <div class="flex items-end justify-between mb-8">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-orange-500 mb-1">Spēļu centrs</p>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Gaidāmās spēles</h2>
                </div>
                <a href="{{ route('local.matches.index') }}"
                   class="hidden sm:flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors">
                    Visas spēles
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            @if(count($matches) > 0)
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($matches as $match)
                    <a href="{{ route('local.matches.show', $match['id']) }}"
                       class="group bg-white rounded-2xl border border-gray-200 hover:border-blue-300 hover:shadow-lg transition-all duration-200 overflow-hidden flex flex-col">

                        {{-- Card top bar --}}
                        <div class="h-1 w-full bg-gradient-to-r from-orange-400 to-blue-500"></div>

                        <div class="p-5 flex-1 flex flex-col">
                            {{-- Tournament badge --}}
                            @if(!empty($match['tournament_name']))
                            <span class="self-start mb-3 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-600 border border-blue-100">
                                {{ $match['tournament_name'] }}
                            </span>
                            @endif

                            {{-- Teams --}}
                            <div class="flex items-center gap-3 mb-4">
                                <div class="flex-1 text-center">
                                    <p class="text-sm font-bold text-gray-900 leading-tight group-hover:text-blue-700 transition-colors">
                                        {{ $match['home_team_name'] }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-0.5">Mājas</p>
                                </div>
                                <div class="flex-shrink-0 w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-xs font-black text-gray-400">
                                    VS
                                </div>
                                <div class="flex-1 text-center">
                                    <p class="text-sm font-bold text-gray-900 leading-tight group-hover:text-blue-700 transition-colors">
                                        {{ $match['away_team_name'] }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-0.5">Viesi</p>
                                </div>
                            </div>

                            {{-- Meta row --}}
                            <div class="mt-auto flex items-center justify-between text-xs text-gray-500">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    @if($match['start_time'])
                                        {{ \Carbon\Carbon::parse($match['start_time'])->translatedFormat('d. M, H:i') }}
                                    @else
                                        —
                                    @endif
                                </div>
                                @if($match['ticket_price'] > 0)
                                <span class="font-semibold text-emerald-600">
                                    €{{ number_format($match['ticket_price'], 2) }}
                                </span>
                                @else
                                <span class="text-gray-400">Bez maksas</span>
                                @endif
                            </div>
                        </div>

                        {{-- CTA footer --}}
                        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs text-gray-400">
                                @if(!empty($match['arena']) && is_array($match['arena']))
                                    {{ $match['arena']['name'] ?? '' }}
                                @elseif(!empty($match['arena']) && is_string($match['arena']))
                                    {{ $match['arena'] }}
                                @endif
                            </span>
                            <span class="text-xs font-medium text-blue-600 group-hover:underline">Skatīt ></span>
                        </div>
                    </a>
                    @endforeach
                </div>

                <div class="mt-8 text-center sm:hidden">
                    <a href="{{ route('local.matches.index') }}"
                       class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                        Visas spēles
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 font-medium">Šobrīd nav ieplānotu spēļu.</p>
                    <p class="text-sm text-gray-400 mt-1">Atgriezies drīzumā!</p>
                </div>
            @endif
        </div>
    </section>

</x-app-layout>