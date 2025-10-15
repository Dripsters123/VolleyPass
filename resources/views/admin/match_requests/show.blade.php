<x-app-layout>
  <div class="max-w-3xl mx-auto mt-10 bg-white shadow-md rounded-lg p-6">
    <h1 class="text-2xl font-bold mb-4 text-blue-700">Pieprasījums #{{ $req->id }}</h1>

    <div class="space-y-3">
      <div><strong>Lietotājs:</strong> {{ $req->user->name }}</div>
      <div><strong>Tips:</strong> {{ $req->request_type }}</div>
      <div><strong>Mājas komanda:</strong> {{ $req->home_team }}</div>
      <div><strong>Viesu komanda:</strong> {{ $req->away_team }}</div>
      <div><strong>Laiks:</strong> {{ $req->start_time->format('Y-m-d H:i') }} — {{ $req->end_time->format('Y-m-d H:i') }}</div>
      <div><strong>Formāts:</strong> {{ $req->players_per_team }}</div>

      {{-- Logos --}}
      <div class="flex items-center gap-4 mt-4">
        <div>
          <strong>Mājas logo:</strong><br>
          @if($req->home_logo)
            <img src="{{ Storage::url($req->home_logo) }}" alt="home_logo" class="w-16 h-16 object-cover rounded">
          @else
            <div class="w-16 h-16 bg-gray-100 flex items-center justify-center text-gray-500 text-sm">nav</div>
          @endif
        </div>
        <div>
          <strong>Viesu logo:</strong><br>
          @if($req->away_logo)
            <img src="{{ Storage::url($req->away_logo) }}" alt="away_logo" class="w-16 h-16 object-cover rounded">
          @else
            <div class="w-16 h-16 bg-gray-100 flex items-center justify-center text-gray-500 text-sm">nav</div>
          @endif
        </div>
      </div>

      {{-- Referee --}}
      @if($req->referee_name)
        <div class="mt-4">
          <strong>Tiesnesis:</strong> {{ $req->referee_name }}
        </div>
      @endif

      {{-- Players --}}
      @if(!empty($req->players_home) || !empty($req->players_away))
        <div class="mt-6">
          <h3 class="font-semibold text-lg mb-2">Spēlētāji</h3>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <strong>Mājas:</strong>
              <ul class="list-disc ml-5 text-sm">
                @foreach($req->players_home ?? [] as $p)
                  <li>{{ $p }}</li>
                @endforeach
              </ul>
            </div>
            <div>
              <strong>Viesi:</strong>
              <ul class="list-disc ml-5 text-sm">
                @foreach($req->players_away ?? [] as $p)
                  <li>{{ $p }}</li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>
      @endif

      {{-- Coaches --}}
      @if($req->coach_home || $req->coach_away)
        <div class="mt-4">
          <strong>Treneri:</strong><br>
          Mājas: {{ $req->coach_home ?? 'nav' }}<br>
          Viesi: {{ $req->coach_away ?? 'nav' }}
        </div>
      @endif

      {{-- Score update --}}
      @if($req->request_type === 'score_update')
        <div class="mt-6 p-3 bg-yellow-50 border rounded">
          <h3 class="font-semibold mb-1">Priekšlikts rezultāts</h3>
          <p class="text-lg font-bold">{{ $req->score_home }} — {{ $req->score_away }}</p>
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
        <button class="px-4 py-2 bg-green-600 text-white rounded">Apstiprināt</button>
      </form>

      <form method="POST" action="{{ route('admin.match_requests.reject', $req->id) }}">
        @csrf
        <button class="px-4 py-2 bg-red-600 text-white rounded">Noraidīt</button>
      </form>

      <a href="{{ route('admin.match_requests.inbox') }}" class="px-4 py-2 bg-gray-200 rounded">Atpakaļ</a>
    </div>
  </div>
</x-app-layout>
