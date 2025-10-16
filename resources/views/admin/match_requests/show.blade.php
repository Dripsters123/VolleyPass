<x-app-layout>
  <div class="max-w-3xl mx-auto mt-10 bg-white shadow-md rounded-lg p-6">
    <h1 class="text-2xl font-bold mb-4 text-blue-700">
      @if(($req->request_type ?? '') === 'score_update')
        Rezultātu pieprasījums #{{ $req->id }}
      @else
        Mača pieprasījums #{{ $req->id }}
      @endif
    </h1>

    @if(session('success'))
      <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
    @endif

    <div class="space-y-3 text-gray-800">
      <div><strong>Lietotājs:</strong> {{ optional($req->user)->name ?? '—' }}</div>
      <div><strong>Tips:</strong> {{ $req->request_type ?? 'match' }}</div>
      <div><strong>Mājas komanda:</strong> {{ $req->home_team ?? '—' }}</div>
      <div><strong>Viesu komanda:</strong> {{ $req->away_team ?? '—' }}</div>

      <div><strong>Laiks:</strong>
        @if(isset($req->start_time) && isset($req->end_time))
          {{ \Carbon\Carbon::parse($req->start_time)->format('Y-m-d H:i') }} — {{ \Carbon\Carbon::parse($req->end_time)->format('Y-m-d H:i') }}
        @elseif(isset($req->start_time))
          {{ \Carbon\Carbon::parse($req->start_time)->format('Y-m-d H:i') }}
        @else
          —
        @endif
      </div>

      <div><strong>Formāts:</strong> {{ $req->players_per_team ?? '—' }}</div>

      <div class="flex items-center gap-6 mt-4">
        <div>
          <strong>Mājas logo:</strong><br>
          @if($req->home_logo)
            <img src="{{ Storage::url($req->home_logo) }}" alt="home_logo" class="w-20 h-20 object-cover rounded">
          @else
            <div class="w-20 h-20 bg-gray-100 flex items-center justify-center text-gray-500 text-sm">nav</div>
          @endif
        </div>

        <div>
          <strong>Viesu logo:</strong><br>
          @if($req->away_logo)
            <img src="{{ Storage::url($req->away_logo) }}" alt="away_logo" class="w-20 h-20 object-cover rounded">
          @else
            <div class="w-20 h-20 bg-gray-100 flex items-center justify-center text-gray-500 text-sm">nav</div>
          @endif
        </div>
      </div>

      @if($req->referee_name)
        <div class="mt-4"><strong>Tiesnesis:</strong> {{ $req->referee_name }}</div>
      @endif

      @if(!empty($req->home_players) || !empty($req->away_players))
        <div class="mt-6">
          <h3 class="font-semibold text-lg mb-2">Spēlētāji</h3>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <strong>Mājas:</strong>
              <ul class="list-disc ml-5 text-sm">
                @foreach(json_decode($req->home_players, true) ?? [] as $p)
                  @if(is_array($p))
                    <li>{{ ($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '') }}</li>
                  @else
                    <li>{{ $p }}</li>
                  @endif
                @endforeach
              </ul>
            </div>

            <div>
              <strong>Viesi:</strong>
              <ul class="list-disc ml-5 text-sm">
                @foreach(json_decode($req->away_players, true) ?? [] as $p)
                  @if(is_array($p))
                    <li>{{ ($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '') }}</li>
                  @else
                    <li>{{ $p }}</li>
                  @endif
                @endforeach
              </ul>
            </div>
          </div>
        </div>
      @endif

      @if($req->coach_home || $req->coach_away)
        <div class="mt-4">
          <strong>Treneri:</strong><br>
          Mājas: {{ $req->coach_home ?? 'nav' }}<br>
          Viesi: {{ $req->coach_away ?? 'nav' }}
        </div>
      @endif

      @if(($req->request_type ?? '') === 'score_update')
        <div class="mt-6 p-3 bg-yellow-50 border rounded">
          <h3 class="font-semibold mb-1">Priekšlikts rezultāts</h3>
          <p class="text-lg font-bold">{{ $req->score_home ?? '0' }} — {{ $req->score_away ?? '0' }}</p>
          <p class="text-sm text-gray-600 mt-1">
            Saistītais mačs:
            @if($req->match)
              <a href="{{ route('local.matches.show', $req->match->id) }}" class="text-blue-600 underline">
                {{ $req->match->home_team_name }} vs {{ $req->match->away_team_name }}
              </a>
            @else
              n/a
            @endif
          </p>
        </div>
      @endif
    </div>

    <div class="mt-6 flex gap-3">
      <form method="POST" action="{{ route('admin.match_requests.accept', $req->id) }}">
        @csrf
        <button type="submit"
                onclick="return confirm('Vai tiešām vēlaties apstiprināt pieprasījumu?');"
                class="px-4 py-2 bg-green-600 text-white rounded">
          Apstiprināt
        </button>
      </form>

      {{-- Reject (admin) --}}
      <form method="POST" action="{{ route('admin.match_requests.reject', $req->id) }}">
        @csrf
        <button type="submit"
                onclick="return confirm('Vai tiešām vēlaties noraidīt pieprasījumu?');"
                class="px-4 py-2 bg-red-600 text-white rounded">
          Noraidīt
        </button>
      </form>

      <a href="{{ route('admin.match_requests.inbox') }}" class="px-4 py-2 bg-gray-200 rounded">Atpakaļ</a>
    </div>
  </div>
</x-app-layout>
