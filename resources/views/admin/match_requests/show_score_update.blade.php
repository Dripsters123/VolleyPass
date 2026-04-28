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
                        Rezultāta pieprasījums #{{ $req->id }}
                    </h2>
                    <p class="text-sm text-gray-400 mt-0.5">
                        Iesniedzējs : {{ optional($req->user)->name ?? '—' }}
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
        @php
            $sets = [];
            if (isset($verification) && $verification) {
                $conf = $verification->confirmations;
                if (!empty($conf['sets']) && is_array($conf['sets'])) {
                    $sets = array_values($conf['sets']);
                }
            }

            $setsWonHome = 0;
            $setsWonAway = 0;
            foreach ($sets as $set) {
                if (($set['home'] ?? 0) > ($set['away'] ?? 0)) {
                    $setsWonHome++;
                } elseif (($set['away'] ?? 0) > ($set['home'] ?? 0)) {
                    $setsWonAway++;
                }
            }

            $fallbackHome = $req->score_home ?? $verification?->home_score;
            $fallbackAway = $req->score_away ?? $verification?->away_score;
        @endphp
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Mačs</h3>

            <div class="flex items-center justify-between gap-4">
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

                <div class="flex-shrink-0 text-center px-4 min-w-[220px]">
                    @if(count($sets))
                        <div class="flex flex-wrap items-center justify-center gap-2 mb-3">
                            @foreach($sets as $i => $set)
                                @php
                                    $homeWonSet = ($set['home'] ?? 0) > ($set['away'] ?? 0);
                                    $awayWonSet = ($set['away'] ?? 0) > ($set['home'] ?? 0);
                                @endphp
                                <div class="rounded-xl border border-gray-200 px-3 py-2 min-w-[68px] bg-gray-50">
                                    <div class="text-[10px] uppercase tracking-wide text-gray-400">{{ $i + 1 }}. sets</div>
                                    <div class="text-sm font-bold text-gray-900">
                                        <span class="{{ $homeWonSet ? 'text-green-600' : 'text-gray-700' }}">{{ $set['home'] ?? '?' }}</span>
                                        <span class="text-gray-300">:</span>
                                        <span class="{{ $awayWonSet ? 'text-green-600' : 'text-gray-700' }}">{{ $set['away'] ?? '?' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="text-lg font-extrabold text-gray-900 tracking-tight">
                            Seti {{ $setsWonHome }} <span class="text-gray-300 font-light">–</span> {{ $setsWonAway }}
                        </div>
                        <div class="text-xs text-gray-400 mt-1">Pieprasītais rezultāts pa setiem</div>
                    @elseif(!is_null($fallbackHome) || !is_null($fallbackAway))
                        <div class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ $fallbackHome ?? '?' }} – {{ $fallbackAway ?? '?' }}</div>
                        <div class="text-xs text-amber-600 mt-1">Pieejams tikai kopsavilkums, nevis setu sadalījums</div>
                    @else
                        <div class="text-2xl font-bold text-gray-300">? – ?</div>
                        <div class="text-xs text-gray-400 mt-1">Nav rezultāta</div>
                    @endif
                </div>

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

            <div class="grid grid-cols-2 gap-3 mt-5 pt-5 border-t border-gray-50 text-sm">
                <div>
                    <span class="text-gray-400 text-xs">Sākums</span>
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

        {{-- Notes --}}
        @if(!empty($req->notes) || !empty($req->description))
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Piezīmes</h3>
            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $req->notes ?? $req->description }}</p>
        </div>
        @endif

        {{-- Admin actions --}}
        @if(in_array($req->status, ['pending', 'reviewing', 'appealed']))
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4" x-data="{ rejectOpen: false }">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Darbības</h3>

            <div class="flex flex-wrap gap-3">
                <form method="POST" action="{{ route('admin.match_requests.accept', $req->id) }}">
                    @csrf
                    <button type="button"
                            onclick="vpConfirm('Apstiprintāt šo rezultāta pieprasījumu?', () => this.closest('form').submit(), { confirmText: 'Apstiprintāt' })"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition shadow-sm text-sm">
                        Apstiprināt
                    </button>
                </form>

                <button @click="rejectOpen = !rejectOpen"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl transition shadow-sm text-sm">
                    Noraidīt
                </button>

                @if($req->status === 'pending')
                <form method="POST" action="{{ route('admin.match_requests.review', $req->id) }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition shadow-sm text-sm">
                        Atzīmēt kā Tiek izskatīts
                    </button>
                </form>
                @endif
            </div>

            <div x-show="rejectOpen" x-transition class="mt-2">
                <form method="POST" action="{{ route('admin.match_requests.reject', $req->id) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Noraidījuma iemesls</label>
                        <textarea name="rejection_reason" rows="3"
                                  class="w-full rounded-xl border-gray-300 text-sm focus:ring-red-500 focus:border-red-500"
                                  placeholder="Paskaidrojiet noraidījuma iemeslu..."></textarea>
                    </div>
                    <button type="submit"
                            class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl transition text-sm">
                        Apstiprināt noraidījumu
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>
</x-app-layout>