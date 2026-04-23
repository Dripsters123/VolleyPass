<x-app-layout>
  <style>
    .arena-canvas {
      position: relative;
      width: 1000px;
      height: 700px;
      min-width: 1000px;
      border: 2px dashed #d1d5db;
      background-color: #f9fafb;
      background-image:
        linear-gradient(rgba(148,163,184,0.32) 1px, transparent 1px),
        linear-gradient(90deg, rgba(148,163,184,0.32) 1px, transparent 1px);
      background-size: 50px 50px;
      overflow: hidden;
      border-radius: 16px;
      box-shadow: inset 0 0 0 1px rgba(148,163,184,0.18);
    }

    .canvas-wrapper {
      overflow: auto;
      border: 1px solid #e2e8f0;
      border-radius: 18px;
      background: #f8fafc;
      padding: 14px;
    }

    .arena-element {
      position: absolute;
      cursor: move;
      user-select: none;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      border-radius: 4px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      transition: box-shadow 0.2s;
    }

    .arena-element:hover {
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .arena-element.selected {
      box-shadow: 0 0 0 3px #3b82f6;
    }

    .seat-element {
      width: 44px;
      height: 44px;
      background-color: #0284c7;
      color: white;
      font-size: 12px;
      border: 2px solid #0369a1;
      border-radius: 10px;
      box-shadow: 0 3px 8px rgba(15, 23, 42, 0.18);
    }

    .court-element {
      width: 260px;
      height: 150px;
      background-color: #f59e0b;
      color: #92400e;
      font-size: 14px;
      border: 2px solid #b45309;
      border-radius: 14px;
      box-shadow: 0 3px 9px rgba(15, 23, 42, 0.16);
    }

    .element-palette {
      display: grid;
      gap: 12px;
    }

    .palette-item {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 12px 16px;
      border: 1px solid #e2e8f0;
      border-radius: 18px;
      cursor: pointer;
      background: white;
      color: #0f172a;
      font-weight: 600;
      transition: transform 0.2s ease, border-color 0.2s ease, background-color 0.2s ease;
      box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
      width: 100%;
      min-height: 48px;
      text-align: center;
      white-space: nowrap;
      -webkit-tap-highlight-color: transparent;
    }

    .palette-item:hover {
      transform: translateY(-1px);
      border-color: #2563eb;
      background-color: #eff6ff;
    }

    .palette-item:active {
      cursor: grabbing;
      transform: translateY(0);
    }

    .palette-item .item-icon {
      width: 28px;
      height: 28px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: #e0f2fe;
      color: #0369a1;
      border-radius: 9999px;
      font-size: 14px;
      font-weight: 700;
    }
  </style>
  <div class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Izveidot mača pieprasījumu</h1>

    @if ($errors->any())
      <div class="mb-4 p-3 rounded bg-red-50 border border-red-200 text-sm text-red-700">
        <ul class="list-disc list-inside">
          @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('match_requests.store') }}" class="space-y-6" enctype="multipart/form-data">
      @csrf

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Mājas komanda</label>
          <input name="home_team" value="{{ old('home_team') }}" class="w-full p-2 border rounded" required placeholder="Piem.: Rīgas Vilki">
          @error('home_team') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium">Viesu komanda</label>
          <input name="away_team" value="{{ old('away_team') }}" class="w-full p-2 border rounded" required placeholder="Piem.: Jūrmalas Viļņi">
          @error('away_team') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Mājas treneris</label>
          <input name="home_coach" value="{{ old('home_coach') }}" class="w-full p-2 border rounded" placeholder="Piem.: Jānis Bērziņš">
        </div>
        <div>
          <label class="block text-sm font-medium">Viesu treneris</label>
          <input name="away_coach" value="{{ old('away_coach') }}" class="w-full p-2 border rounded" placeholder="Piem.: Pēteris Kalniņš">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium">Tiesneši (komatu atdalīti)</label>
        <input name="judges" value="{{ old('judges') }}" class="w-full p-2 border rounded" placeholder="Piem.: Anna Ozola, Kārlis Liepa">
      </div>

      <div>
        <label class="block text-sm font-medium">Vieta — pilna adrese</label>
        <input name="location" value="{{ old('location') }}" class="w-full p-2 border rounded" placeholder="Piem.: Sporta iela 2, Rīga, LV-1001">
      </div>

      <div class="max-w-xs">
        <label class="block text-sm font-medium">Ieteicamā biļetes cena (EUR)</label>
        <input type="number" step="0.01" min="0" name="ticket_price" value="{{ old('ticket_price') }}"
               class="w-full p-2 border rounded" placeholder="Piem.: 5.00">
        <p class="text-xs text-gray-500 mt-1">Administrators var mainīt šo cenu pirms apstiprināšanas.</p>
      </div>

      <div class="max-w-xs">
        <label class="block text-sm font-medium">Formāts</label>
        <select id="players_per_team" name="players_per_team" class="w-full p-2 border rounded" required>
          <option value="2" {{ old('players_per_team') == 2 ? 'selected' : '' }}>2 pret 2</option>
          <option value="4" {{ old('players_per_team') == 4 ? 'selected' : '' }}>4 pret 4</option>
          <option value="6" {{ old('players_per_team') == 6 ? 'selected' : '' }}>6 pret 6</option>
        </select>
        @error('players_per_team') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
      </div>

      @if($teams->isNotEmpty())
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border border-blue-100 bg-blue-50 rounded-xl p-4">
          <div>
              <label class="block text-sm font-medium text-blue-800 mb-1">Ielādēt mājas komandu</label>
              <select id="loadHomeTeam" class="w-full p-2 border border-blue-200 rounded-lg text-sm bg-white">
                  <option value="">— izvēlēties saglabātu komandu —</option>
                  @foreach($teams as $t)
                      <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->players_per_team }}v{{ $t->players_per_team }})</option>
                  @endforeach
              </select>
          </div>
          <div>
              <label class="block text-sm font-medium text-blue-800 mb-1">Ielādēt viesu komandu</label>
              <select id="loadAwayTeam" class="w-full p-2 border border-blue-200 rounded-lg text-sm bg-white">
                  <option value="">— izvēlēties saglabātu komandu —</option>
                  @foreach($teams as $t)
                      <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->players_per_team }}v{{ $t->players_per_team }})</option>
                  @endforeach
              </select>
          </div>
      </div>
      @endif

      <div id="playerFields" class="space-y-4"></div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Sākuma laiks</label>
          <input type="datetime-local" name="start_time" value="{{ old('start_time') }}" class="w-full p-2 border rounded" required>
          @error('start_time') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium">Beigu laiks</label>
          <input type="datetime-local" name="end_time" value="{{ old('end_time') }}" class="w-full p-2 border rounded" required>
          @error('end_time') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Mājas komandas logo</label>
          <input type="file" name="home_logo" accept="image/jpeg,image/png,image/svg+xml,image/webp">
          @error('home_logo') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium">Viesu komandas logo</label>
          <input type="file" name="away_logo" accept="image/jpeg,image/png,image/svg+xml,image/webp">
          @error('away_logo') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
      </div>

      <!-- Arena Selection Section -->
      <div class="border-t pt-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-4">
          <div>
            <h2 class="text-xl font-semibold">Arēna</h2>
            <p class="text-sm text-gray-600 mt-1">Lūdzu izvēlieties saglabātu arēnu vai izveidojiet jaunu, pirms nosūtāt mača pieprasījumu.</p>
          </div>
          <a href="{{ route('arenas.create') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-orange-500 px-5 py-3 text-sm font-semibold text-white shadow-lg hover:opacity-95">
            + Izveidot jaunu arēnu
          </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-1 bg-white rounded-3xl border border-gray-200 p-5 shadow-sm">
            <div class="mb-5">
              <h3 class="text-lg font-semibold">Atlasītā arēna</h3>
              <p class="text-sm text-gray-500 mt-1">Šī arēna tiks pievienota jūsu pieprasījumam.</p>
            </div>
            <div id="selectedArenaSummary" class="rounded-3xl border border-dashed border-gray-300 bg-gray-50 p-4 min-h-[180px] text-sm text-gray-600 flex items-center justify-center">
              Nav atlasīta arēna.
            </div>
            <div class="mt-5 space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Arēnas nosaukums</label>
                <input id="arena_name" name="arena_name" value="{{ old('arena_name') }}" required class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-blue-500 focus:ring-blue-200 focus:ring-2" placeholder="Piem.: Rīgas Sporta Halles Sektors A">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Apraksts</label>
                <input id="arena_description" name="arena_description" value="{{ old('arena_description') }}" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-blue-500 focus:ring-blue-200 focus:ring-2" placeholder="Pievienot aprakstu (pēc izvēles)">
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Platums (metri)</label>
                  <input type="number" step="0.1" min="1" name="arena_width_m" value="{{ old('arena_width_m') }}" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-blue-500 focus:ring-blue-200 focus:ring-2" placeholder="Piem.: 30">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Garums (metri)</label>
                  <input type="number" step="0.1" min="1" name="arena_height_m" value="{{ old('arena_height_m') }}" class="w-full rounded-2xl border border-gray-300 px-4 py-3 focus:border-blue-500 focus:ring-blue-200 focus:ring-2" placeholder="Piem.: 18">
                </div>
              </div>
              <div class="rounded-3xl bg-blue-50 border border-blue-100 p-4 text-sm text-blue-700">
                Lai pārliecinātos, ka pieprasījums satur arēnu, vispirms izvēlieties saglabātu plānojumu vai izveidojiet jaunu arēnu sadaļā <strong>Manas Arēnas</strong>.
              </div>
            </div>
          </div>

          <div class="lg:col-span-2 bg-white rounded-3xl border border-gray-200 p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
              <div>
                <h3 class="text-lg font-semibold">Jūsu arēnas</h3>
                <p class="text-sm text-gray-500">Izvēlieties saglabātu arēnu no vēstures.</p>
              </div>
              <button id="resetArenaSelection" type="button" class="rounded-full border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Notīrīt izvēli</button>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
              @forelse($arenas as $arena)
                <button type="button" data-arena-id="{{ $arena->id }}" class="arena-selection-card group rounded-3xl border border-gray-200 p-4 text-left transition hover:border-blue-400 hover:shadow-lg">
                  <div class="flex items-center justify-between gap-3">
                    <div>
                      <h4 class="text-base font-semibold text-slate-900">{{ $arena->name }}</h4>
                      <p class="text-sm text-gray-500 mt-1">{{ $arena->width }} x {{ $arena->height }} px</p>
                    </div>
                    <span class="inline-flex rounded-full bg-blue-600 px-3 py-1 text-xs font-semibold text-white">Izvēlēties</span>
                  </div>
                  @if($arena->description)
                    <p class="mt-4 text-sm text-gray-500">{{ Str::limit($arena->description, 80) }}</p>
                  @endif
                </button>
              @empty
                <div class="col-span-full rounded-3xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center text-gray-500">
                  Nav saglabātu arēnu. <a href="{{ route('arenas.create') }}" class="text-blue-600 font-semibold hover:underline">Izveidot pirmo arēnu</a>.
                </div>
              @endforelse
            </div>
          </div>
        </div>
      </div>

      <!-- Hidden inputs for arena data -->
      <input type="hidden" name="arena_id" id="arena_id_input" value="{{ old('arena_id') }}">
      <input type="hidden" name="arena_layout" id="arena-layout-input" value='{{ old('arena_layout') ? json_encode(old('arena_layout')) : '' }}'>
      <input type="hidden" name="arena_elements" id="arena-elements-input" value='{{ old('arena_elements') ? json_encode(old('arena_elements')) : '' }}'>
      <input type="hidden" name="arena_width" id="arena_width_input" value="{{ old('arena_width', 1000) }}">
      <input type="hidden" name="arena_height" id="arena_height_input" value="{{ old('arena_height', 700) }}">

      <div>
        <button class="bg-blue-600 text-white px-4 py-3 rounded-2xl font-semibold shadow hover:bg-blue-700">Nosūtīt pieprasījumu</button>
      </div>
    </form>
  </div>

  <script>
    (function(){
      const select = document.getElementById('players_per_team');
      const container = document.getElementById('playerFields');

      const oldHome = @json(old('home_players', []));
      const oldAway = @json(old('away_players', []));

      function renderPlayers(n) {
        n = Number(n) || 2;
        let html = '';
        html += '<div class="bg-gray-50 border rounded p-4">';
        html += '<h3 class="font-semibold mb-2">Mājas komanda</h3>';
        for (let i=0;i<n;i++){
          const hf = (oldHome[i] && oldHome[i].first_name) ? oldHome[i].first_name : '';
          const hl = (oldHome[i] && oldHome[i].last_name) ? oldHome[i].last_name : '';
          html += `
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">
              <div>
                <label class="text-sm">Vārds</label>
                <input name="home_players[${i}][first_name]" value="${hf}" class="w-full p-2 border rounded" required>
              </div>
              <div>
                <label class="text-sm">Uzvārds</label>
                <input name="home_players[${i}][last_name]" value="${hl}" class="w-full p-2 border rounded" required>
              </div>
            </div>
          `;
        }
        html += '</div>';

        html += '<div class="bg-gray-50 border rounded p-4">';
        html += '<h3 class="font-semibold mb-2">Viesu komanda</h3>';
        for (let i=0;i<n;i++){
          const af = (oldAway[i] && oldAway[i].first_name) ? oldAway[i].first_name : '';
          const al = (oldAway[i] && oldAway[i].last_name) ? oldAway[i].last_name : '';
          html += `
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">
              <div>
                <label class="text-sm">Vārds</label>
                <input name="away_players[${i}][first_name]" value="${af}" class="w-full p-2 border rounded" required>
              </div>
              <div>
                <label class="text-sm">Uzvārds</label>
                <input name="away_players[${i}][last_name]" value="${al}" class="w-full p-2 border rounded" required>
              </div>
            </div>
          `;
        }
        html += '</div>';
        container.innerHTML = html;
      }

      renderPlayers(select.value);
      select.addEventListener('change', e => renderPlayers(e.target.value));

      // Saved team data
      @php
        $teamsById = $teams->keyBy('id')->map(function ($t) {
            return ['name' => $t->name, 'coach' => $t->coach, 'players_per_team' => $t->players_per_team, 'players' => $t->players];
        });
      @endphp
      const savedTeams = @json($teamsById);

      function applyTeam(teamId, side) {
        const team = savedTeams[teamId];
        if (!team) return;
        // Set format to match team
        select.value = team.players_per_team;
        renderPlayers(team.players_per_team);
        // Fill team name
        const nameInput = document.querySelector(`input[name="${side}_team"]`);
        if (nameInput && !nameInput.value) nameInput.value = team.name;
        // Fill coach
        const coachInput = document.querySelector(`input[name="${side}_coach"]`);
        if (coachInput && !coachInput.value) coachInput.value = team.coach || '';
        // Fill players
        (team.players || []).forEach((p, i) => {
          const fn = document.querySelector(`input[name="${side}_players[${i}][first_name]"]`);
          const ln = document.querySelector(`input[name="${side}_players[${i}][last_name]"]`);
          if (fn) fn.value = p.first_name || '';
          if (ln) ln.value = p.last_name || '';
        });
      }

      const homeTeamSel = document.getElementById('loadHomeTeam');
      const awayTeamSel = document.getElementById('loadAwayTeam');
      if (homeTeamSel) homeTeamSel.addEventListener('change', e => { if (e.target.value) applyTeam(e.target.value, 'home'); });
      if (awayTeamSel) awayTeamSel.addEventListener('change', e => { if (e.target.value) applyTeam(e.target.value, 'away'); });
    })();

    // Arena Selection JavaScript
    document.addEventListener('DOMContentLoaded', function() {
      const arenaCards = document.querySelectorAll('.arena-selection-card');
      const selectedArenaSummary = document.getElementById('selectedArenaSummary');
      const arenaIdInput = document.getElementById('arena_id_input');
      const arenaLayoutInput = document.getElementById('arena-layout-input');
      const arenaElementsInput = document.getElementById('arena-elements-input');
      const arenaWidthInput = document.getElementById('arena_width_input');
      const arenaHeightInput = document.getElementById('arena_height_input');
      const arenaNameInput = document.getElementById('arena_name');
      const arenaDescriptionInput = document.getElementById('arena_description');
      const resetArenaSelection = document.getElementById('resetArenaSelection');

      @php
          $arenaPayload = $arenas->map(function ($arena) {
              return [
                  'id' => $arena->id,
                  'name' => $arena->name,
                  'description' => $arena->description,
                  'layout' => $arena->layout,
                  'elements' => $arena->elements,
                  'width' => $arena->width,
                  'height' => $arena->height,
              ];
          });
      @endphp
      const arenas = @json($arenaPayload);

      function updateSelectedArena(arena) {
        if (!arena) {
          selectedArenaSummary.innerHTML = '<div class="text-gray-500">Nav atlasīta arēna.</div>';
          arenaIdInput.value = '';
          arenaLayoutInput.value = '';
          arenaElementsInput.value = '';
          arenaWidthInput.value = 1000;
          arenaHeightInput.value = 700;
          arenaCards.forEach(card => card.classList.remove('ring-2', 'ring-blue-500', 'border-blue-500'));
          return;
        }

        selectedArenaSummary.innerHTML = `
          <div class="space-y-3">
            <div class="text-sm font-semibold text-slate-900">${arena.name}</div>
            <div class="text-sm text-gray-600">${arena.description || 'Bez apraksta'}</div>
            <div class="flex flex-wrap gap-2 text-xs text-slate-500">
              <span class="px-2 py-1 rounded-full bg-slate-100">${arena.width} x ${arena.height} px</span>
              <span class="px-2 py-1 rounded-full bg-slate-100">Saglabāta arēna</span>
            </div>
          </div>
        `;

        arenaIdInput.value = arena.id;
        arenaLayoutInput.value = JSON.stringify(arena.layout || []);
        arenaElementsInput.value = JSON.stringify(arena.elements || []);
        arenaWidthInput.value = arena.width || 1000;
        arenaHeightInput.value = arena.height || 700;
        arenaNameInput.value = arena.name;
        arenaDescriptionInput.value = arena.description || '';

        arenaCards.forEach(card => {
          const isSelected = Number(card.dataset.arenaId) === arena.id;
          card.classList.toggle('ring-2', isSelected);
          card.classList.toggle('ring-blue-500', isSelected);
          card.classList.toggle('border-blue-500', isSelected);
        });
      }

      arenaCards.forEach(card => {
        card.addEventListener('click', function() {
          const arenaId = Number(this.dataset.arenaId);
          const arena = arenas.find(a => a.id === arenaId);
          if (!arena) return;
          updateSelectedArena(arena);
        });
      });

      resetArenaSelection?.addEventListener('click', function() {
        updateSelectedArena(null);
      });

      const preselectedArenaId = Number('{{ old('arena_id', '') }}');
      if (preselectedArenaId) {
        const arena = arenas.find(a => a.id === preselectedArenaId);
        if (arena) {
          updateSelectedArena(arena);
        }
      }
    });
  </script>
</x-app-layout>
