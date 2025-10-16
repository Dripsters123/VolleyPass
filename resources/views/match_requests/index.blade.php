<x-app-layout>
  <div class="max-w-5xl mx-auto p-6 bg-white rounded mt-8 shadow">
    <h1 class="text-2xl font-bold mb-4 text-blue-700">Mani pieprasījumi</h1>

    <form method="GET" class="mb-4 flex gap-2 items-center">
      <select name="type" class="p-2 border rounded">
        <option value="">Visi</option>
        <option value="create_match" {{ request('type')=='create_match' ? 'selected' : '' }}>Izveidot maču</option>
        <option value="score_update" {{ request('type')=='score_update' ? 'selected' : '' }}>Rezultāts</option>
      </select>
      <button class="px-3 py-1 bg-blue-600 text-white rounded">Filtrēt</button>
    </form>

    @if($requests->isEmpty())
      <p class="text-gray-600">Jūs vēl neesat nosūtījis nevienu pieprasījumu.</p>
    @else
      <div class="space-y-4">
        @foreach($requests as $r)
          @if(request('type') && request('type') != $r->request_type) @continue @endif
          <div class="p-4 border rounded-md bg-gray-50 hover:bg-gray-100 transition">
            <div class="flex items-center justify-between flex-wrap">
              <div>
                <div class="font-semibold text-lg">{{ $r->home_team }} vs {{ $r->away_team }}</div>
                <div class="text-sm text-gray-600">
                  {{ $r->start_time->format('Y-m-d H:i') }} — {{ $r->end_time->format('Y-m-d H:i') }}
                </div>
                <div class="text-sm mt-1">Tips: {{ $r->request_type }} — Statuss: {{ ucfirst($r->status) }}</div>
              </div>

              <div class="text-right mt-2 sm:mt-0">
                <div class="mt-2 flex gap-2 justify-end items-center">
                  <a href="{{ route('match_requests.view', $r->id) }}" class="px-3 py-1 bg-blue-600 text-white rounded text-sm">Skatīt</a>
                  @if($r->status === 'pending')
                    <a href="{{ route('match_requests.edit', $r->id) }}" class="px-3 py-1 bg-yellow-500 text-white rounded text-sm">Rediģēt</a>
                    <form method="POST" action="{{ route('match_requests.cancel', $r->id) }}" onsubmit="return confirm('Atcelt pieprasījumu?');">
                      @csrf @method('DELETE')
                      <button class="px-3 py-1 bg-red-600 text-white rounded text-sm">Atcelt</button>
                    </form>
                  @endif
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>
</x-app-layout>
