<x-app-layout>
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
          <input name="home_team" value="{{ old('home_team') }}" class="w-full p-2 border rounded" required>
          @error('home_team') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium">Viesu komanda</label>
          <input name="away_team" value="{{ old('away_team') }}" class="w-full p-2 border rounded" required>
          @error('away_team') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Mājas treneris</label>
          <input name="home_coach" value="{{ old('home_coach') }}" class="w-full p-2 border rounded">
        </div>
        <div>
          <label class="block text-sm font-medium">Viesu treneris</label>
          <input name="away_coach" value="{{ old('away_coach') }}" class="w-full p-2 border rounded">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium">Tiesneši (komatu atdalīti)</label>
        <input name="judges" value="{{ old('judges') }}" class="w-full p-2 border rounded">
      </div>

      <div>
        <label class="block text-sm font-medium">Vieta (piem., Cēsis, Latvia)</label>
        <input name="location" value="{{ old('location') }}" class="w-full p-2 border rounded">
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
          <input type="file" name="home_logo" accept="image/*">
          @error('home_logo') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium">Viesu komandas logo</label>
          <input type="file" name="away_logo" accept="image/*">
          @error('away_logo') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
      </div>

      <div>
        <button class="bg-blue-600 text-white px-4 py-2 rounded">Nosūtīt pieprasījumu</button>
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
    })();
  </script>
</x-app-layout>
