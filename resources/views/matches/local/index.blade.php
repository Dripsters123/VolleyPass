<x-app-layout>
  <div class="container mx-auto py-4 px-3">

    <div class="mb-3">
      <div class="flex items-center justify-between gap-3">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-blue-700">Volejbola mači</h1>
          <p class="text-sm text-gray-600 mt-1">Apskati tuvākos mačus, filtrē un izvēlies savu vietu.</p>
        </div>

        {{-- Mobile Filter Toggle --}}
        <div class="flex items-center gap-2">
          <button id="mobileFilterToggle" class="sm:hidden inline-flex items-center gap-2 px-3 py-2 border rounded bg-white shadow text-sm">
            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path d="M3 5h14v2H3V5zM6 11h8v2H6v-2z"/></svg>
            Filtri
          </button>
        </div>
      </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">

      <aside class="hidden lg:block w-full max-w-xs flex-shrink-0 border rounded-lg p-4 bg-gray-50 h-fit sticky top-20">
        <form method="GET" action="{{ route('local.matches.index') }}" class="space-y-3">

          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Komanda</label>
            <input type="text" name="team_name" value="{{ request('team_name') }}" placeholder="Meklēt pēc komandas" class="w-full p-2 border rounded text-sm focus:ring focus:ring-blue-300"/>
          </div>
          
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Treneris</label>
            <input type="text" name="coach_name" value="{{ request('coach_name') }}" placeholder="Meklēt pēc trenera" class="w-full p-2 border rounded text-sm focus:ring focus:ring-blue-300"/>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Vieta</label>
            <input type="text" name="location" value="{{ request('location') }}" placeholder="Meklēt pēc vietas" class="w-full p-2 border rounded text-sm focus:ring focus:ring-blue-300"/>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Formāts</label>
            <select name="players_per_team" class="w-full p-2 border rounded text-sm">
              <option value="">Visi</option>
              <option value="2" {{ request('players_per_team') == 2 ? 'selected' : '' }}>2 pret 2</option>
              <option value="4" {{ request('players_per_team') == 4 ? 'selected' : '' }}>4 pret 4</option>
              <option value="6" {{ request('players_per_team') == 6 ? 'selected' : '' }}>6 pret 6</option>
            </select>
          </div>
       
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Datums no</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full p-2 border rounded text-sm"/>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Datums līdz</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full p-2 border rounded text-sm"/>
          </div>

         
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Maks. cena (€)</label>
            <input type="number" step="0.01" name="max_price" value="{{ request('max_price') }}" class="w-full p-2 border rounded text-sm"/>
          </div>

          
          <div class="pt-2 flex gap-3">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 flex-1">Filtrēt</button>
            <a href="{{ route('local.matches.index') }}" class="border border-gray-300 px-4 py-2 rounded-md text-sm hover:bg-gray-100 flex-1 text-center">Notīrīt</a>
          </div>
        </form>
      </aside>

     
      <main class="flex-1">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5 justify-center">
          @forelse($matches as $match)
            <article class="rounded-lg overflow-hidden transition duration-150 flex flex-col
              {{ ($match->match_state ?? $match->status_type) === 'completed' ? 'bg-red-50 border border-red-100 opacity-90' : 'bg-white shadow hover:shadow-lg' }}">

              <div class="p-3 flex-1 flex flex-col min-w-0">
                <div class="flex items-start justify-between gap-3">
                  <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                      {{-- Home team --}}
                      @if($match->home_logo)
                        <img src="{{ Storage::url($match->home_logo) }}" alt="home" class="w-8 h-8 object-cover rounded"/>
                      @else
                        <div class="w-8 h-8 rounded bg-gray-200 flex items-center justify-center text-xs text-gray-600">H</div>
                      @endif
                      <h3 class="font-semibold text-sm text-gray-800 truncate">{{ $match->home_team_name }}</h3>

                      <span class="text-gray-400">vs</span>

                      {{-- Away team --}}
                      @if($match->away_logo)
                        <img src="{{ Storage::url($match->away_logo) }}" alt="away" class="w-8 h-8 object-cover rounded"/>
                      @else
                        <div class="w-8 h-8 rounded bg-gray-200 flex items-center justify-center text-xs text-gray-600">V</div>
                      @endif
                      <h3 class="font-semibold text-sm text-gray-800 truncate">{{ $match->away_team_name }}</h3>
                    </div>

                    <div class="text-xs text-gray-500 mt-1 truncate">{{ \Carbon\Carbon::parse($match->start_time)->format('Y-m-d H:i') ?? '-' }}</div>
                  </div>

                  <div class="text-right flex-shrink-0">
                    <div class="text-sm text-gray-800 font-medium">Cena</div>
                    <div class="text-blue-700 font-semibold text-lg">€{{ number_format($match->ticket_price ?? 0, 2) }}</div>
                  </div>
                </div>

                {{-- Scores / Details --}}
                @if(($match->match_state ?? $match->status_type) === 'completed')
                  <div class="mt-3 text-sm">
                    <div class="flex items-center gap-4">
                      <div class="flex-1 text-right">
                        <div class="text-xs text-gray-500">Mājas rezultāts</div>
                        <div class="text-lg font-semibold text-red-700">{{ $match->home_score ?? '-' }}</div>
                      </div>
                      <div class="px-2 text-gray-500">—</div>
                      <div class="flex-1 text-left">
                        <div class="text-xs text-gray-500">Viesu rezultāts</div>
                        <div class="text-lg font-semibold text-red-700">{{ $match->away_score ?? '-' }}</div>
                      </div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Mačs pabeigts</div>
                  </div>
                @else
                  <div class="mt-3 text-xs text-gray-700 space-y-1 truncate">
                    @if($match->location)
                      <div><strong>Adrese:</strong> {{ $match->location }}</div>
                    @endif
                    @if($match->home_coach || $match->away_coach)
                      <div><strong>Treneri:</strong> {{ $match->home_coach ?? '-' }} / {{ $match->away_coach ?? '-' }}</div>
                    @endif
                    @if(is_array($match->judges) && count($match->judges))
                      <div><strong>Tiesneši:</strong> {{ implode(', ', $match->judges) }}</div>
                    @endif
                  </div>
                @endif
              </div>

              <div class="p-3 border-t flex gap-2">
                <a href="{{ route('local.matches.show', $match->id) }}" class="flex-1 text-center bg-indigo-600 text-white py-2 rounded text-sm hover:bg-indigo-700 transition">
                  Apskatīt
                </a>
              </div>
            </article>
          @empty
            <p class="col-span-full text-gray-600 text-center">Nav pieejami mači.</p>
          @endforelse
        </div>

      
        <div class="mt-6 flex justify-center">
          {{ $matches->appends(request()->query())->links() }}
        </div>
      </main>
    </div>
  </div>

  
  <div id="mobileFilterDrawer" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50" id="mobileFilterBackdrop"></div>
    <div class="absolute top-0 left-0 right-0 bg-white shadow p-4" style="max-height:90vh; overflow:auto;">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold text-lg">Filtri</h2>
        <button id="mobileFilterClose" class="px-2 py-1 bg-gray-200 rounded">Aizvērt</button>
      </div>
      <form method="GET" action="{{ route('local.matches.index') }}" class="space-y-3">

        {{-- Mobile filter inputs --}}
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Komanda</label>
          <input type="text" name="team_name" value="{{ request('team_name') }}" placeholder="Meklēt pēc komandas" class="w-full p-2 border rounded text-sm"/>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Treneris</label>
          <input type="text" name="coach_name" value="{{ request('coach_name') }}" placeholder="Meklēt pēc trenera" class="w-full p-2 border rounded text-sm"/>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Vieta</label>
          <input type="text" name="location" value="{{ request('location') }}" placeholder="Meklēt pēc vietas" class="w-full p-2 border rounded text-sm"/>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Formāts</label>
          <select name="players_per_team" class="w-full p-2 border rounded text-sm">
            <option value="">Visi</option>
            <option value="2" {{ request('players_per_team') == 2 ? 'selected' : '' }}>2 pret 2</option>
            <option value="4" {{ request('players_per_team') == 4 ? 'selected' : '' }}>4 pret 4</option>
            <option value="6" {{ request('players_per_team') == 6 ? 'selected' : '' }}>6 pret 6</option>
          </select>
        </div>
        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">No</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full p-2 border rounded text-sm"/>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Līdz</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full p-2 border rounded text-sm"/>
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Maks. cena (€)</label>
          <input type="number" step="0.01" name="max_price" value="{{ request('max_price') }}" class="w-full p-2 border rounded text-sm"/>
        </div>
        <div class="flex gap-2">
          <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded text-sm hover:bg-blue-700">Filtrēt</button>
          <a href="{{ route('local.matches.index') }}" class="flex-1 border border-gray-300 py-2 rounded text-center text-sm hover:bg-gray-50">Notīrīt</a>
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
