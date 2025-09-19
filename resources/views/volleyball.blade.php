<x-app-layout>
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold mb-4">Upcoming Volleyball Matches</h1>

        <!-- Filter Form -->
        <form id="filter-form" class="mb-6 flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tournament</label>
                <input type="text" id="tournament" placeholder="Tournament name"
                       class="mt-1 border rounded px-3 py-2 w-full">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Home Team</label>
                <input type="text" id="home_team" placeholder="Home team"
                       class="mt-1 border rounded px-3 py-2 w-full">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Away Team</label>
                <input type="text" id="away_team" placeholder="Away team"
                       class="mt-1 border rounded px-3 py-2 w-full">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Date</label>
                <input type="date" id="date" class="mt-1 border rounded px-3 py-2 w-full">
            </div>

            <button type="button" id="filter-button" class="mt-6 bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                Filter
            </button>
        </form>

        <!-- Matches List -->
        <div id="upcoming-matches">
            @if(!empty($matches))
                <ul class="space-y-4">
                    @foreach($matches as $match)
                        @if(isset($match['id'], $match['home_team_name'], $match['away_team_name']))
                            <li class="bg-white p-4 rounded shadow">
                                <div class="flex items-center gap-3 flex-wrap">
                                    @if(!empty($match['home_team_hash_image']))
                                        <img src="https://images.sportdevs.com/{{ $match['home_team_hash_image'] }}.png" class="h-6 w-6">
                                    @endif
                                    <strong>{{ $match['home_team_name'] }}</strong>
                                    <span class="text-gray-500">vs</span>
                                    <strong>{{ $match['away_team_name'] }}</strong>
                                    @if(!empty($match['away_team_hash_image']))
                                        <img src="https://images.sportdevs.com/{{ $match['away_team_hash_image'] }}.png" class="h-6 w-6">
                                    @endif
                                </div>

                                <div class="text-sm text-gray-500 mt-1">
                                    {{ $match['tournament_name'] ?? 'Tournament' }} • 
                                    {{ \Carbon\Carbon::parse($match['start_time'])->format('d M Y, H:i') }}
                                </div>

                                <a href="{{ route('volleyball.show', ['id' => $match['id']]) }}" class="mt-3 inline-block px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
                                    More Info
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            @else
                <div class="bg-white p-4 rounded shadow">
                    No upcoming matches.
                </div>
            @endif
        </div>
    </div>

    <script src="{{ asset('js/volleyball.js') }}"></script>
</x-app-layout>
