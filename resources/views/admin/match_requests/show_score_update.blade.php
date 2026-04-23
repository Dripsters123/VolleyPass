{{-- resources/views/admin/match_requests/show_score_update.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.match_requests.inbox') }}"
                   class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Rezultata pieprasijums #{{ $req->id }}
                    </h2>
                    <p class="text-sm text-gray-400 mt-0.5">
                        Iesniedzejs: {{ optional($req->user)->name ?? '—' }}
                        @if(optional($req->user)->email) · {{ optional($req->user)->email }} @endif
                    </p>
                </div>
            </div>

            @php
                $statusMap = [
                    'pending'   => ['label' => 'Gaida', 'class' => 'bg-yellow-100 text-yellow-800 border-yellow-200'],
                    'reviewing' => ['label' => 'Tiek izskatits', 'class' => 'bg-blue-100 text-blue-800 border-blue-200'],
                    'accepted'  => ['label' => 'Apstiprinats', 'class' => 'bg-green-100 text-green-800 border-green-200'],
                    'rejected'  => ['label' => 'Noraidits', 'class' => 'bg-red-100 text-red-800 border-red-200'],
                    'appealed'  => ['label' => 'Apelacija', 'class' => 'bg-purple-100 text-purple-800 border-purple-200'],
                ];
                $st = $statusMap[$req->status ?? 'pending'] ?? $statusMap['pending'];
            @endphp
            <span class="px-3 py-1 rounded-full text-sm font-semibold border {{ $st['class'] }}">{{ $st['label'] }}</span>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm">{{ session('error') }}</div>
        @endif

        {{-- Teams + score card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Macs</h3>

            <div class="flex items-center justify-between gap-4">
                {{-- Home team --}}
                <div class="flex-1 text-center">
                    @if($req->home_logo)
                        <img src="{{ asset('storage/' . $req->home_logo) }}" class="w-16 h-16 rounded-xl object-cover mx-auto mb-2 border" alt="">
                    @else
                        <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 mx-auto mb-2 flex items-center justify-center">
                            <span class="text-white text-2xl font-bold">{{ strtoupper(substr($req->home_team ?? 'H', 0, 1)) }}</span>
                        </div>
                    @endif
                    <p class="font-bold text-gray-900 text-sm">{{ $req->home_team ?? '—' }}</p>
                    @if($req->home_coach)
                        <p class="text-xs text-gray-400">{{ $req->home_coach }}</p>
                    @endif
                </div>

                {{-- Score --}}
                @php
                    $scoreDisplay = null;
                    $scoreSource  = null;
                    if (isset($verification) && $verification) {
                        $h = $verification->home_score;
                        $a = $verification->away_score;
                        if (!is_null($h) || !is_null($a)) {
                            $scoreDisplay = ($h ?? '?') . ' - ' . ($a ?? '?');
                            $scoreSource  = 'verification';
                        }
                    }
                    if (!$scoreDisplay) {
                        if (!empty($req->requested_home_score) || !empty($req->requested_away_score)) {
                            $scoreDisplay = ($req->requested_home_score ?? '?') . ' - ' . ($req->requested_away_score ?? '?');
                            $scoreSource  = 'request_fields';
                        } elseif (!empty($req->requested_score)) {
                            $scoreDisplay = $req->requested_score;
                            $scoreSource  = 'request_fields';
                        }
                    }
                    if (!$scoreDisplay) {
                        $candidate = $req->notes ?? $req->description ?? '';
                        if ($candidate && preg_match('/\b\d+\s*(?:[:\-])\s*\d+(?:\s*(?:,|\|)\s*\d+\s*(?:[:\-])\s*\d+)*\b/', $candidate, $m)) {
                            $scoreDisplay = $m[0];
                            $scoreSource  = 'notes';
                        }
                    }
                @endphp
                <div class="flex-shrink-0 text-center px-6">
                    @if($scoreDisplay)
                        <div class="text-4xl font-extrabold text-gray-900 tracking-tight">{{ $scoreDisplay }}</div>
                        <div class="text-xs text-gray-400 mt-1">
                            @if($scoreSource === 'verification') Verificets @else Pieprasitais rezultats @endif
                        </div>
                    @else
                        <div class="text-2xl font-bold text-gray-300">? - ?</div>
                        <div class="text-xs text-gray-400 mt-1">Nav rezultata</div>
                    @endif
                </div>

                {{-- Away team --}}
                <div class="flex-1 text-center">
                    @if($req->away_logo)
                        <img src="{{ asset('storage/' . $req->away_logo) }}" class="w-16 h-16 rounded-xl object-cover mx-auto mb-2 border" alt="">
                    @else
                        <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-orange-400 to-red-500 mx-auto mb-2 flex items-center justify-center">
                            <span class="text-white text-2xl font-bold">{{ strtoupper(substr($req->away_team ?? 'V', 0, 1)) }}</span>
                        </div>
                    @endif
                    <p class="font-bold text-gray-900 text-sm">{{ $req->away_team ?? '—' }}</p>
                    @if($req->away_coach)
                        <p class="text-xs text-gray-400">{{ $req->away_coach }}</p>
                    @endif
                </div>
            </div>

            {{-- Times --}}
            <div class="grid grid-cols-2 gap-3 mt-5 pt-5 border-t border-gray-50 text-sm">
                <div>
                    <span class="text-gray-400 text-xs">Sakums</span>
                    <p class="font-medium text-gray-800">
                        @if($req->start_time)
                            {{ \Carbon\Carbon::parse($req->start_time)->timezone('Europe/Riga')->format('d.m.Y H:i') }}
                        @else — @endif
                    </p>
                </div>
                <div>
                    <span class="text-gray-400 text-xs">Beigas</span>
                    <p class="font-medium text-gray-800">
                        @if($req->end_time)
                            {{ \Carbon\Carbon::parse($req->end_time)->timezone('Europe/Riga')->format('d.m.Y H:i') }}
                        @else — @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Player rosters --}}
        @php
            $homePlayers = is_array($req->home_players) ? $req->home_players : json_decode($req->home_players ?? '[]', true);
            $awayPlayers = is_array($req->away_players) ? $req->away_players : json_decode($req->away_players ?? '[]', true);
            $homePlayers = is_array($homePlayers) ? $homePlayers : [];
            $awayPlayers = is_array($awayPlayers) ? $awayPlayers : [];
        @endphp

        @if(count($homePlayers) || count($awayPlayers))
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Spelataji</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">{{ $req->home_team ?? 'Majas komanda' }}</h4>
                    <ol class="space-y-1">
                        @forelse($homePlayers as $i => $p)
                            <li class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $i+1 }}</span>
                                <span class="text-sm text-gray-700">{{ trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')) }}</span>
                            </li>
                        @empty
                            <li class="text-sm text-gray-400">Nav spelataju</li>
                        @endforelse
                    </ol>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">{{ $req->away_team ?? 'Viesu komanda' }}</h4>
                    <ol class="space-y-1">
                        @forelse($awayPlayers as $i => $p)
                            <li class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-orange-100 text-orange-700 text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $i+1 }}</span>
                                <span class="text-sm text-gray-700">{{ trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')) }}</span>
                            </li>
                        @empty
                            <li class="text-sm text-gray-400">Nav spelataju</li>
                        @endforelse
                    </ol>
                </div>
            </div>
        </div>
        @endif

        {{-- Notes --}}
        @if(!empty($req->notes) || !empty($req->description))
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Piezimes</h3>
            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $req->notes ?? $req->description }}</p>
        </div>
        @endif

        {{-- Admin actions --}}
        @if(in_array($req->status, ['pending', 'reviewing', 'appealed']))
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4" x-data="{ rejectOpen: false }">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Darbibas</h3>

            <div class="flex flex-wrap gap-3">
                <form method="POST" action="{{ route('admin.match_requests.accept', $req->id) }}"
                      onsubmit="return confirm('Apstiprinat so rezultata pieprasijumu?');">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition shadow-sm text-sm">
                        Apstiprinat
                    </button>
                </form>

                <button @click="rejectOpen = !rejectOpen"
                        class="inline-flex items-center gap-2 px-5 py-2.5 border border-red-200 hover:bg-red-50 text-red-600 font-medium rounded-xl transition text-sm">
                    Noraidit
                </button>

                @if($req->status === 'pending')
                <form method="POST" action="{{ route('admin.match_requests.review', $req->id) }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 border border-blue-200 hover:bg-blue-50 text-blue-600 font-medium rounded-xl transition text-sm">
                        Atzimet kā Tiek izskatits
                    </button>
                </form>
                @endif
            </div>

            <div x-show="rejectOpen" x-transition class="mt-2">
                <form method="POST" action="{{ route('admin.match_requests.reject', $req->id) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Noraidijuma iemesls</label>
                        <textarea name="rejection_reason" rows="3"
                                  class="w-full rounded-xl border-gray-300 text-sm focus:ring-red-500 focus:border-red-500"
                                  placeholder="Paskaidrojiet noraidijuma iemeslu..."></textarea>
                    </div>
                    <button type="submit"
                            class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl transition text-sm">
                        Apstiprinat noraidijumu
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>
</x-app-layout>