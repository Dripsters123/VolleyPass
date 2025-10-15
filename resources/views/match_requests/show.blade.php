<x-app-layout>
    <div class="max-w-3xl mx-auto mt-10 bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-6 text-blue-700">
            Mača pieprasījuma informācija
        </h1>

        <div class="space-y-3 text-gray-700">
            <div>
                <span class="font-semibold">Mājas komanda:</span>
                {{ $matchRequest->home_team }}
            </div>

            <div>
                <span class="font-semibold">Viesu komanda:</span>
                {{ $matchRequest->away_team }}
            </div>

            <div>
                <span class="font-semibold">Sākuma laiks:</span>
                {{ $matchRequest->start_time->format('Y-m-d H:i') }}
            </div>

            <div>
                <span class="font-semibold">Beigu laiks:</span>
                {{ $matchRequest->end_time->format('Y-m-d H:i') }}
            </div>

            <div>
                <span class="font-semibold">Formāts:</span>
                {{ $matchRequest->players_per_team }} pret {{ $matchRequest->players_per_team }}
            </div>

            <div>
                <span class="font-semibold">Statuss:</span>
                <span class="font-medium 
                    {{ $matchRequest->status === 'pending' ? 'text-yellow-600' : 
                       ($matchRequest->status === 'accepted' ? 'text-green-600' : 'text-red-600') }}">
                    {{ ucfirst($matchRequest->status) }}
                </span>
            </div>
        </div>

        <!-- Players section -->
        <div class="mt-6 grid md:grid-cols-2 gap-6">
            <div>
                <h2 class="text-lg font-semibold mb-2 text-blue-600">Mājas spēlētāji</h2>
                <ul class="list-disc list-inside text-gray-700">
                    @foreach($matchRequest->home_players as $p)
                        <li>{{ $p['first_name'] }} {{ $p['last_name'] }}</li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-2 text-blue-600">Viesu spēlētāji</h2>
                <ul class="list-disc list-inside text-gray-700">
                    @foreach($matchRequest->away_players as $p)
                        <li>{{ $p['first_name'] }} {{ $p['last_name'] }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Action buttons -->
        <div class="mt-8 flex justify-between items-center">
            <a href="{{ route('match_requests.my') }}"
               class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
               ← Atpakaļ
            </a>

            <div class="flex gap-3">
                @if($matchRequest->status === 'pending')
                    <a href="{{ route('match_requests.edit', $matchRequest->id) }}"
                       class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                       Rediģēt
                    </a>

                    <form method="POST"
                          action="{{ route('match_requests.cancel', $matchRequest->id) }}"
                          onsubmit="return confirm('Vai tiešām vēlaties atcelt šo pieprasījumu?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                            Atcelt
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
