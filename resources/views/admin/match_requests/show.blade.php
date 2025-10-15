<x-app-layout>
  <div class="max-w-3xl mx-auto mt-10 bg-white shadow-md rounded-lg p-6">
    <header class="mb-6">
      <h1 class="text-2xl font-bold text-blue-700">Pieprasījums #{{ $req->id }}</h1>
      <p class="text-sm text-gray-600 mt-1">
        Iesniedzējs: <strong>{{ optional($req->user)->name ?? '—' }}</strong>
        &nbsp;—&nbsp; status: <span class="font-medium">{{ ucfirst($req->status ?? 'pending') }}</span>
      </p>
    </header>

    @php
      // defensive decoding & normalisation
      $playersHome = is_string($req->home_players) ? json_decode($req->home_players, true) ?? [] : ($req->home_players ?? []);
      $playersAway = is_string($req->away_players) ? json_decode($req->away_players, true) ?? [] : ($req->away_players ?? []);
      $coachHome = $req->home_coach ?? $req->coach_home ?? null;
      $coachAway = $req->away_coach ?? $req->coach_away ?? null;
      $referee = $req->referee_name ?? $req->judge ?? null;
    @endphp

    <div class="grid grid-cols-1 gap-6">
      <div class="space-y-2">
        <div class="text-sm text-gray-600"><strong>Tips:</strong> {{ $req->request_type ?? '—' }}</div>
        <div class="text-sm text-gray-600"><strong>Laiks:</strong>
          @if(isset($req->start_time) && isset($req->end_time))
            {{ \Carbon\Carbon::parse($req->start_time)->timezone('Europe/Riga')->format('d.m.Y H:i') }}
            — 
            {{ \Carbon\Carbon::parse($req->end_time)->timezone('Europe/Riga')->format('d.m.Y H:i') }}
          @else
            —
          @endif
        </div>
        <div class="text-sm text-gray-600"><strong>Formāts:</strong> {{ $req->players_per_team ?? '—' }}</div>
        <div class="text-sm text-gray-600"><strong>Vieta:</strong> {{ $req->location ?? 'nav' }}</div>
      </div>

      <div class="flex items-center gap-6">
        <div class="flex items-center gap-4">
          <div>
            <div class="text-xs text-gray-500">Mājas logo</div>
            @if($req->home_logo)
              <img src="{{ Storage::url($req->home_logo) }}" alt="home_logo" class="w-20 h-20 object-cover rounded">
            @else
              <div class="w-20 h-20 bg-gray-100 flex items-center justify-center text-gray-500 text-sm rounded">nav</div>
            @endif
          </div>

          <div>
            <div class="text-xs text-gray-500">Viesu logo</div>
            @if($req->away_logo)
              <img src="{{ Storage::url($req->away_logo) }}" alt="away_logo" class="w-20 h-20 object-cover rounded">
            @else
              <div class="w-20 h-20 bg-gray-100 flex items-center justify-center text-gray-500 text-sm rounded">nav</div>
            @endif
          </div>
        </div>

        <div class="ml-auto text-sm text-gray-600">
          <div><strong>Iesniegts:</strong> {{ optional($req->created_at) ? \Carbon\Carbon::parse($req->created_at)->timezone('Europe/Riga')->format('d.m.Y H:i') : '—' }}</div>
          <div class="mt-1"><strong>Kontaktinfo:</strong> {{ optional($req->user)->email ?? '—' }}</div>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-6">
        <div class="bg-gray-50 p-4 rounded">
          <h3 class="font-semibold text-gray-700">Mājas</h3>
          <div class="mt-2">
            <div class="font-medium">{{ $req->home_team ?? '—' }}</div>
            <div class="text-sm text-gray-600 mt-1">Treneris: {{ $coachHome ?? 'nav' }}</div>
          </div>

          @if(!empty($playersHome))
            <div class="mt-3 text-sm">
              <div class="font-semibold mb-1">Spēlētāji</div>
              <ul class="list-disc pl-5 text-sm space-y-1">
                @foreach($playersHome as $p)
                  <li>{{ is_array($p) ? ($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '') : $p }}</li>
                @endforeach
              </ul>
            </div>
          @endif
        </div>

        <div class="bg-gray-50 p-4 rounded">
          <h3 class="font-semibold text-gray-700">Viesi</h3>
          <div class="mt-2">
            <div class="font-medium">{{ $req->away_team ?? '—' }}</div>
            <div class="text-sm text-gray-600 mt-1">Treneris: {{ $coachAway ?? 'nav' }}</div>
          </div>

          @if(!empty($playersAway))
            <div class="mt-3 text-sm">
              <div class="font-semibold mb-1">Spēlētāji</div>
              <ul class="list-disc pl-5 text-sm space-y-1">
                @foreach($playersAway as $p)
                  <li>{{ is_array($p) ? ($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '') : $p }}</li>
                @endforeach
              </ul>
            </div>
          @endif
        </div>
      </div>

      @if($req->request_type === 'score_update')
        <div class="p-4 bg-yellow-50 border rounded">
          <h3 class="font-semibold">Priekšlikts rezultāts</h3>
          <p class="text-lg font-bold mt-2">{{ $req->score_home ?? '—' }} — {{ $req->score_away ?? '—' }}</p>
          <p class="text-sm text-gray-600 mt-1">
            Saistītais mačs:
            @if(optional($req->match)->id)
              <a href="{{ route('local.matches.show', $req->match->id) }}" class="text-blue-600 underline">
                {{ $req->match->home_team_name }} vs {{ $req->match->away_team_name }}
              </a>
            @else
              n/a
            @endif
          </p>
        </div>
      @endif

      <div class="mt-4 flex gap-3">
        {{-- POST to accept — controller will set status and then redirect to admin.matches.create?request_id= --}}
        <form method="POST" action="{{ route('admin.match_requests.accept', $req->id) }}">
          @csrf
          <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            Apstiprināt un atvērt rediģēšanai
          </button>
        </form>

        <a href="{{ route('admin.matches.create', ['request_id' => $req->id]) }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
          Rediģēt (neapstiprinot)
        </a>

        {{-- Reject --}}
        <form method="POST" action="{{ route('admin.match_requests.reject', $req->id) }}">
          @csrf
          <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
            Noraidīt
          </button>
        </form>

        <a href="{{ route('admin.match_requests.inbox') }}" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Atpakaļ uz sarakstu</a>
      </div>
    </div>
  </div>
</x-app-layout>
