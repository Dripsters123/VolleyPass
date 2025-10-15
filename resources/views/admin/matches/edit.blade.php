<x-app-layout>
  <div class="max-w-3xl mx-auto p-6 bg-white rounded mt-8">
    <h1 class="text-2xl font-bold mb-4">Rediģēt maču</h1>

    @php $p = $prefill ?? []; @endphp

    <form method="POST" action="{{ route('admin.matches.update', $match->id) }}" class="space-y-6" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <input type="hidden" name="match_id" value="{{ $match->id }}">

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Mājas komanda</label>
          <input name="home_team_name" value="{{ old('home_team_name', $p['home_team_name'] ?? '') }}" class="w-full p-2 border rounded" required>
          @error('home_team_name') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium">Viesu komanda</label>
          <input name="away_team_name" value="{{ old('away_team_name', $p['away_team_name'] ?? '') }}" class="w-full p-2 border rounded" required>
          @error('away_team_name') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Mājas treneris</label>
          <input name="home_coach" value="{{ old('home_coach', $p['home_coach'] ?? '') }}" class="w-full p-2 border rounded">
          @error('home_coach') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium">Viesu treneris</label>
          <input name="away_coach" value="{{ old('away_coach', $p['away_coach'] ?? '') }}" class="w-full p-2 border rounded">
          @error('away_coach') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium">Tiesneši (komatu atdalīti)</label>
        <input name="judges" value="{{ old('judges', is_array($p['judges'] ?? null) ? implode(', ', $p['judges']) : ($p['judges'] ?? '')) }}" class="w-full p-2 border rounded">
        @error('judges') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium">Vieta</label>
        <input name="location" value="{{ old('location', $p['location'] ?? '') }}" class="w-full p-2 border rounded">
        @error('location') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
      </div>

      <div class="max-w-xs">
        <label class="block text-sm font-medium">Formāts</label>
        <select id="players_per_team" name="players_per_team" class="w-full p-2 border rounded" required>
          <option value="2" {{ (old('players_per_team', $p['players_per_team'] ?? '') == 2) ? 'selected' : '' }}>2 pret 2</option>
          <option value="4" {{ (old('players_per_team', $p['players_per_team'] ?? '') == 4) ? 'selected' : '' }}>4 pret 4</option>
          <option value="6" {{ (old('players_per_team', $p['players_per_team'] ?? '') == 6) ? 'selected' : '' }}>6 pret 6</option>
        </select>
        @error('players_per_team') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
      </div>

      <div id="playerFields" class="space-y-4"></div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Sākuma laiks</label>
          <input type="datetime-local" name="start_time" value="{{ old('start_time', $p['start_time'] ?? '') }}" class="w-full p-2 border rounded" required>
          @error('start_time') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium">Beigu laiks</label>
          <input type="datetime-local" name="end_time" value="{{ old('end_time', $p['end_time'] ?? '') }}" class="w-full p-2 border rounded">
          @error('end_time') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium">Biļešu cena (EUR)</label>
        <input name="ticket_price" type="number" step="0.01" min="0" value="{{ old('ticket_price', $p['ticket_price'] ?? '') }}" class="w-40 p-2 border rounded" required>
        @error('ticket_price') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Mājas komandas logo</label>
          @if(!empty($p['home_logo']))
            <div class="mb-2">
              <img src="{{ \Illuminate\Support\Facades\Storage::url($p['home_logo']) }}" class="w-20 h-20 object-cover rounded" alt="home logo">
            </div>
          @elseif(!empty($match->home_logo))
            <div class="mb-2">
              <img src="{{ \Illuminate\Support\Facades\Storage::url($match->home_logo) }}" class="w-20 h-20 object-cover rounded" alt="home logo">
            </div>
          @endif
          <input type="file" name="home_logo" accept="image/*">
          @error('home_logo') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium">Viesu komandas logo</label>
          @if(!empty($p['away_logo']))
            <div class="mb-2">
              <img src="{{ \Illuminate\Support\Facades\Storage::url($p['away_logo']) }}" class="w-20 h-20 object-cover rounded" alt="away logo">
            </div>
          @elseif(!empty($match->away_logo))
            <div class="mb-2">
              <img src="{{ \Illuminate\Support\Facades\Storage::url($match->away_logo) }}" class="w-20 h-20 object-cover rounded" alt="away logo">
            </div>
          @endif
          <input type="file" name="away_logo" accept="image/*">
          @error('away_logo') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
      </div>

      <div>
        <button class="bg-blue-600 text-white px-4 py-2 rounded">Saglabāt izmaiņas</button>
      </div>
    </form>
  </div>

  <script>
    (function(){
      const select = document.getElementById('players_per_team');
      const container = document.getElementById('playerFields');
      const preHome = {!! json_encode($p['home_players'] ?? []) !!};
      const preAway = {!! json_encode($p['away_players'] ?? []) !!};

      function renderPlayers(n) {
        n = Number(n) || 2;
        let html = '';
        html += '<div class="bg-gray-50 border rounded p-4">';
        html += '<h3 class="font-semibold mb-2">Mājas komanda</h3>';
        for (let i=0;i<n;i++){
          const hf = (preHome[i]?.first_name) ?? '';
          const hl = (preHome[i]?.last_name) ?? '';
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
          const af = (preAway[i]?.first_name) ?? '';
          const al = (preAway[i]?.last_name) ?? '';
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
    })();
  </script>
</x-app-layout>
