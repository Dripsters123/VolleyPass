<x-app-layout>
  <div class="max-w-3xl mx-auto bg-white rounded p-6 mt-10 shadow">
    <h1 class="text-2xl font-bold mb-4 text-blue-700">Rediģēt mača pieprasījumu</h1>

    @if ($errors->any())
      <div class="mb-4 p-3 rounded bg-red-50 border border-red-200 text-red-700 text-sm">
        <ul class="list-disc list-inside">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('match_requests.update', $request->id) }}" method="POST" class="space-y-4" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-gray-700 font-medium">Mājas komanda</label>
          <input type="text" name="home_team" value="{{ old('home_team', $request->home_team) }}"
                 class="w-full border rounded px-3 py-2" required>
        </div>

        <div>
          <label class="block text-gray-700 font-medium">Viesu komanda</label>
          <input type="text" name="away_team" value="{{ old('away_team', $request->away_team) }}"
                 class="w-full border rounded px-3 py-2" required>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <div>
          <label class="block text-gray-700 font-medium">Sākuma laiks</label>
          <input type="datetime-local" name="start_time" value="{{ old('start_time', $request->start_time->format('Y-m-d\TH:i')) }}"
                 class="w-full border rounded px-3 py-2" required>
        </div>

        <div>
          <label class="block text-gray-700 font-medium">Beigu laiks</label>
          <input type="datetime-local" name="end_time" value="{{ old('end_time', $request->end_time->format('Y-m-d\TH:i')) }}"
                 class="w-full border rounded px-3 py-2" required>
        </div>
      </div>

      <div class="mt-4">
        <label class="block text-gray-700 font-medium">Formāts</label>
        <select id="players_per_team" name="players_per_team" class="w-full border rounded px-3 py-2">
          @foreach([2, 4, 6] as $n)
            <option value="{{ $n }}" {{ (old('players_per_team', $request->players_per_team) == $n) ? 'selected' : '' }}>
              {{ $n }} pret {{ $n }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
        <div>
          <label class="block text-gray-700 font-medium">Mājas treneris</label>
          <input type="text" name="home_coach" value="{{ old('home_coach', $request->home_coach) }}" class="w-full border rounded px-3 py-2">
        </div>
        <div>
          <label class="block text-gray-700 font-medium">Viesu treneris</label>
          <input type="text" name="away_coach" value="{{ old('away_coach', $request->away_coach) }}" class="w-full border rounded px-3 py-2">
        </div>
      </div>

      <div class="mt-3">
        <label class="block text-gray-700 font-medium">Tiesneši (komatu atdalīti)</label>
        <input type="text" name="judges" value="{{ old('judges', is_array($request->judges) ? implode(', ', $request->judges) : ($request->judges ?? '')) }}"
               class="w-full border rounded px-3 py-2">
      </div>

      <div class="mt-3">
        <label class="block text-gray-700 font-medium">Vieta</label>
        <input type="text" name="location" value="{{ old('location', $request->location) }}" class="w-full border rounded px-3 py-2">
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
        <div>
          <label class="block text-gray-700 font-medium">Mājas komandas logo</label>
          @if($request->home_logo)
            <div class="mb-2"><img src="{{ \Illuminate\Support\Facades\Storage::url($request->home_logo) }}" class="w-20 h-20 object-cover rounded"></div>
          @endif
          <input type="file" name="home_logo" class="w-full">
        </div>
        <div>
          <label class="block text-gray-700 font-medium">Viesu komandas logo</label>
          @if($request->away_logo)
            <div class="mb-2"><img src="{{ \Illuminate\Support\Facades\Storage::url($request->away_logo) }}" class="w-20 h-20 object-cover rounded"></div>
          @endif
          <input type="file" name="away_logo" class="w-full">
        </div>
      </div>

      {{-- Player name fields rendered by JS below; old() will repopulate on validation errors --}}
      <div id="playerFields"></div>

      <div class="mt-6 flex justify-end gap-3">
        <a href="{{ route('match_requests.my') }}" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Atpakaļ</a>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Saglabāt izmaiņas</button>
      </div>
    </form>
  </div>

  <script>
    (function() {
      const select = document.getElementById('players_per_team');
      const container = document.getElementById('playerFields');

      const preHome = {!! json_encode(old('home_players', $request->home_players ?? [])) !!};
      const preAway = {!! json_encode(old('away_players', $request->away_players ?? [])) !!};

      function renderPlayers(n) {
        n = Number(n) || 2;
        let html = '<div class="bg-gray-50 border rounded p-4"><h3 class="font-semibold mb-2">Mājas komanda</h3>';
        for (let i=0;i<n;i++){
          const hf = preHome[i]?.first_name ?? '';
          const hl = preHome[i]?.last_name ?? '';
          html += `
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">
              <div><label class="text-sm">Vārds</label><input name="home_players[${i}][first_name]" value="${hf}" class="w-full p-2 border rounded" required></div>
              <div><label class="text-sm">Uzvārds</label><input name="home_players[${i}][last_name]" value="${hl}" class="w-full p-2 border rounded" required></div>
            </div>
          `;
        }
        html += '</div>';

        html += '<div class="bg-gray-50 border rounded p-4"><h3 class="font-semibold mb-2">Viesu komanda</h3>';
        for (let i=0;i<n;i++){
          const af = preAway[i]?.first_name ?? '';
          const al = preAway[i]?.last_name ?? '';
          html += `
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">
              <div><label class="text-sm">Vārds</label><input name="away_players[${i}][first_name]" value="${af}" class="w-full p-2 border rounded" required></div>
              <div><label class="text-sm">Uzvārds</label><input name="away_players[${i}][last_name]" value="${al}" class="w-full p-2 border rounded" required></div>
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
