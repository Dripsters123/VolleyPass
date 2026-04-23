<x-app-layout title="Volejbola mači – VolleyPass">

  {{-- Page header --}}
  <section class="bg-gray-950 border-b border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex items-center justify-between gap-4">
      <div>
        <p class="text-xs font-semibold uppercase tracking-widest text-orange-400 mb-1">Spēļu centrs</p>
        <h1 class="text-2xl font-extrabold text-white tracking-tight">Volejbola mači</h1>
      </div>
      <button id="mobileFilterToggle"
          class="sm:hidden inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium bg-white/10 text-white hover:bg-white/15 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 4h18M7 10h10M10 16h4"/>
        </svg>
        Filtri
      </button>
    </div>
  </section>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">

      {{-- Filter sidebar --}}
      <aside class="hidden lg:block w-72 shrink-0">
        <div class="bg-white rounded-2xl border border-gray-200 p-5 sticky top-24">
          <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-4">Filtri</p>
          <form method="GET" action="{{ route('local.matches.index') }}" class="space-y-4">
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1.5">Komanda</label>
              <input type="text" name="team_name" value="{{ request('team_name') }}" placeholder="Meklēt pēc komandas"
                   class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 transition-colors">
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1.5">Treneris</label>
              <input type="text" name="coach_name" value="{{ request('coach_name') }}" placeholder="Meklēt pēc trenera"
                   class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 transition-colors">
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1.5">Vieta</label>
              <input type="text" name="location" value="{{ request('location') }}" placeholder="Meklēt pēc vietas"
                   class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 transition-colors">
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1.5">Formāts</label>
              <select name="players_per_team"
                  class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:border-blue-400 transition-colors">
                <option value="">Visi</option>
                <option value="2" {{ request('players_per_team') == 2 ? 'selected' : '' }}>2 pret 2</option>
                <option value="4" {{ request('players_per_team') == 4 ? 'selected' : '' }}>4 pret 4</option>
                <option value="6" {{ request('players_per_team') == 6 ? 'selected' : '' }}>6 pret 6</option>
              </select>
            </div>

            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">No</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                     class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:border-blue-400 transition-colors">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Līdz</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                     class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:border-blue-400 transition-colors">
              </div>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1.5">Maks. cena (€)</label>
              <input type="number" step="0.01" name="max_price" value="{{ request('max_price') }}"
                   class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:border-blue-400 transition-colors">
            </div>

            <div class="flex gap-2 pt-1">
              <button type="submit"
                  class="flex-1 py-2.5 rounded-xl text-sm font-semibold bg-gray-900 text-white hover:bg-gray-800 transition-colors">
                Filtrēt
              </button>
              <a href="{{ route('local.matches.index') }}"
                 class="flex-1 py-2.5 rounded-xl text-sm font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 text-center transition-colors">
                Notīrīt
              </a>
            </div>
          </form>
        </div>
      </aside>

      {{-- Match grid --}}
      <main class="flex-1">

        {{-- Tab navigation --}}
        <div class="flex gap-1 bg-gray-100 p-1 rounded-xl mb-5 w-fit">
          @foreach(['upcoming' => 'Gaidāmie', 'results_pending' => 'Rezultāti gaidāmi', 'completed' => 'Pabeigti'] as $key => $label)
            <a href="{{ route('local.matches.index', array_merge(request()->except('tab', 'page'), ['tab' => $key])) }}"
               class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all
               {{ ($tab ?? 'upcoming') === $key ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
              {{ $label }}
            </a>
          @endforeach
        </div>

        <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">
          @forelse($matches as $match)
            @php
              $effectiveState = $match->effective_state;
              $done = $effectiveState === 'completed';
              $resultsPending = $effectiveState === 'results_pending';
            @endphp
            <a href="{{ route('local.matches.show', $match->id) }}"
               class="group bg-white rounded-2xl border {{ $done ? 'border-gray-200 opacity-75' : ($resultsPending ? 'border-amber-200' : 'border-gray-200 hover:border-blue-300 hover:shadow-lg') }} transition-all duration-200 overflow-hidden flex flex-col">

              <div class="h-1 w-full {{ $done ? 'bg-gray-300' : ($resultsPending ? 'bg-amber-400' : 'bg-gradient-to-r from-orange-400 to-blue-500') }}"></div>

              <div class="p-5 flex-1 flex flex-col">
                {{-- Status badge --}}
                @if($done)
                  <span class="self-start mb-3 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-red-50 text-red-600 border border-red-100">
                    Pabeigts
                  </span>
                @elseif($resultsPending)
                  <span class="self-start mb-3 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                    ⏳ Rezultāti gaidāmi
                  </span>
                @elseif($match->tournament_name ?? false)
                  <span class="self-start mb-3 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-600 border border-blue-100">
                    {{ $match->tournament_name }}
                  </span>
                @endif

                {{-- Teams --}}
                <div class="flex items-center gap-3 mb-4">
                  <div class="flex-1 text-center">
                    @if($match->home_logo)
                      <img src="{{ Storage::url($match->home_logo) }}" class="w-8 h-8 object-cover rounded-full mx-auto mb-1">
                    @else
                      <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-xs font-bold text-orange-600 mx-auto mb-1">
                        {{ strtoupper(substr($match->home_team_name, 0, 2)) }}
                      </div>
                    @endif
                    <p class="text-xs font-bold text-gray-900 leading-tight group-hover:text-blue-700 transition-colors truncate">
                      {{ $match->home_team_name }}
                    </p>
                  </div>
                  <div class="shrink-0 w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-black text-gray-400">
                    @if($done)
                      {{ $match->home_score }}–{{ $match->away_score }}
                    @elseif($resultsPending)
                      ?–?
                    @else
                      VS
                    @endif
                  </div>
                  <div class="flex-1 text-center">
                    @if($match->away_logo)
                      <img src="{{ Storage::url($match->away_logo) }}" class="w-8 h-8 object-cover rounded-full mx-auto mb-1">
                    @else
                      <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-600 mx-auto mb-1">
                        {{ strtoupper(substr($match->away_team_name, 0, 2)) }}
                      </div>
                    @endif
                    <p class="text-xs font-bold text-gray-900 leading-tight group-hover:text-blue-700 transition-colors truncate">
                      {{ $match->away_team_name }}
                    </p>
                  </div>
                </div>

                {{-- Date & price --}}
                <div class="mt-auto flex items-center justify-between text-xs text-gray-500">
                  <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ \Carbon\Carbon::parse($match->start_time)->format('d.m.Y H:i') }}
                  </div>
                  @if(!$done && !$resultsPending)
                    <span class="font-semibold text-emerald-600">€{{ number_format($match->ticket_price ?? 0, 2) }}</span>
                  @endif
                </div>
              </div>

              <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                <span class="text-xs text-gray-400 truncate">{{ $match->location ?? '' }}</span>
                <span class="text-xs font-medium {{ $done ? 'text-gray-400' : ($resultsPending ? 'text-amber-600' : 'text-blue-600 group-hover:underline') }}">
                  @if($done) Pabeigts @elseif($resultsPending) Rezultāti gaidāmi @else Apskatīt → @endif
                </span>
              </div>
            </a>
          @empty
            <div class="col-span-full flex flex-col items-center py-20 text-center">
              <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M8 21h8M12 17v4M5 3h14l-1 8a6 6 0 01-12 0L5 3z"/>
                </svg>
              </div>
              <p class="text-gray-500 font-medium">Nav pieejami mači</p>
              <p class="text-sm text-gray-400 mt-1">Mēģināt filtrēt citādi.</p>
            </div>
          @endforelse
        </div>

        <div class="mt-8 flex justify-center">
          {{ $matches->appends(request()->query())->links() }}
        </div>
      </main>
    </div>
  </div>

  {{-- Mobile filter drawer --}}
  <div id="mobileFilterDrawer" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" id="mobileFilterBackdrop"></div>
    <div class="absolute top-0 left-0 right-0 bg-white rounded-b-2xl shadow-2xl p-5" style="max-height:90vh;overflow:auto;">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-bold text-gray-900">Filtri</h2>
        <button id="mobileFilterClose" class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
      <form method="GET" action="{{ route('local.matches.index') }}" class="space-y-4">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1.5">Komanda</label>
          <input type="text" name="team_name" value="{{ request('team_name') }}" placeholder="Meklēt pēc komandas"
               class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1.5">Treneris</label>
          <input type="text" name="coach_name" value="{{ request('coach_name') }}" placeholder="Meklēt pēc trenera"
               class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1.5">Vieta</label>
          <input type="text" name="location" value="{{ request('location') }}" placeholder="Meklēt pēc vietas"
               class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1.5">Formāts</label>
          <select name="players_per_team" class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm">
            <option value="">Visi</option>
            <option value="2" {{ request('players_per_team') == 2 ? 'selected' : '' }}>2 pret 2</option>
            <option value="4" {{ request('players_per_team') == 4 ? 'selected' : '' }}>4 pret 4</option>
            <option value="6" {{ request('players_per_team') == 6 ? 'selected' : '' }}>6 pret 6</option>
          </select>
        </div>
        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1.5">No</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                 class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm">
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1.5">Līdz</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                 class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm">
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1.5">Maks. cena (€)</label>
          <input type="number" step="0.01" name="max_price" value="{{ request('max_price') }}"
               class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm">
        </div>
        <div class="flex gap-2 pt-1">
          <button type="submit" class="flex-1 py-2.5 rounded-xl text-sm font-semibold bg-gray-900 text-white">Filtrēt</button>
          <a href="{{ route('local.matches.index') }}" class="flex-1 py-2.5 rounded-xl text-sm font-medium border border-gray-200 text-gray-600 text-center">Notīrīt</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    (function(){
      const openBtn = document.getElementById('mobileFilterToggle');
      const drawer = document.getElementById('mobileFilterDrawer');
      const closeBtn = document.getElementById('mobileFilterClose');
      const backdrop = document.getElementById('mobileFilterBackdrop');
      if (!openBtn || !drawer) return;
      openBtn.addEventListener('click', () => drawer.classList.remove('hidden'));
      closeBtn?.addEventListener('click', () => drawer.classList.add('hidden'));
      backdrop?.addEventListener('click', () => drawer.classList.add('hidden'));
    })();
  </script>
</x-app-layout>
