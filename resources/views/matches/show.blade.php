<x-app-layout>
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold mb-6">Spēles detaļas</h1>

        {{-- Match info --}}
        <div class="bg-white p-6 rounded shadow space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    @if(!empty($match['home_team_hash_image']))
                        <img src="https://images.sportdevs.com/{{ $match['home_team_hash_image'] }}.png"
                             alt="{{ $match['home_team_name'] }}" class="h-10 w-10">
                    @endif
                    <strong>{{ $match['home_team_name'] }}</strong>
                    <span class="text-gray-500">pret</span>
                    <strong>{{ $match['away_team_name'] }}</strong>
                    @if(!empty($match['away_team_hash_image']))
                        <img src="https://images.sportdevs.com/{{ $match['away_team_hash_image'] }}.png"
                             alt="{{ $match['away_team_name'] }}" class="h-10 w-10">
                    @endif
                </div>
                <span class="text-sm text-gray-600">
                    {{ \Carbon\Carbon::parse($match['start_time'])->timezone('Europe/Riga')->format('d M Y, H:i T') }}
                </span>
            </div>

            {{-- Tournament / League --}}
            <div class="grid grid-cols-2 gap-4 text-sm text-gray-700">
                <div>
                    <strong>Turnīrs:</strong> {{ $match['tournament_name'] ?? 'Nav zināms' }}<br>
                    <strong>Sezona:</strong> {{ $match['season_name'] ?? 'Nav zināma' }}
                </div>
                <div>
                    <strong>Līga:</strong> {{ $match['league_name'] ?? 'Nav zināma' }}<br>
                    <strong>Statuss:</strong> {{ $match['status_type'] ?? 'Nav zināms' }}
                </div>
            </div>
        </div>

        {{-- Arena --}}
        @if(!empty($match['arena']))
            <div class="bg-gray-50 p-4 rounded-lg shadow-inner text-sm text-gray-700 mt-6">
                <h2 class="font-semibold text-lg mb-2">Arēna</h2>
                <div class="flex items-center gap-4">
                    @if(!empty($match['arena']['hash_image']))
                        <img src="https://images.sportdevs.com/{{ $match['arena']['hash_image'] }}.png"
                             alt="{{ $match['arena']['name'] }}" class="h-16 w-16 rounded">
                    @endif
                    <div>
                        <strong>{{ $match['arena']['name'] ?? 'Nav zināms' }}</strong><br>
                        {{ $match['arena']['city'] ?? 'Pilsēta nav zināma' }}, {{ $match['arena']['country_name'] ?? 'Valsts nav zināma' }}<br>
                        <strong>Kapacitāte:</strong> {{ $match['arena']['stadium_capacity'] ?? 'Nav zināma' }}
                    </div>
                </div>
            </div>
        @endif

        {{-- Ticket price --}}
        <div class="mt-4 bg-yellow-50 p-4 rounded shadow text-lg font-semibold">
            Biļetes cena: <span id="ticketPrice">{{ number_format($match['ticket_price'] ?? 10, 2) }} EUR</span>
        </div>

        {{-- Buy button --}}
        <div class="mt-6">
           <button id="buyTicketBtn"
        data-match-id="{{ $match['id'] }}"
        data-ticket-price="{{ $match['ticket_price'] ?? 10 }}"
        data-taken-seats='@json($takenSeats ?? [])'
        data-seat-prices='@json($seatPrices ?? (object)[])'
        class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
    Pirkt biļeti
</button>

        </div>

        {{-- Seat selection modal --}}
        <div id="seatSelectionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded shadow w-full max-w-7xl h-[95vh] flex flex-col relative overflow-hidden">
                <button id="modalCloseBtn" class="absolute top-3 right-3 text-gray-600 hover:text-gray-900">✕</button>
                <h2 class="text-xl font-bold mb-4">Izvēlies savu vietu</h2>
                <div id="seatMap" class="flex-1 p-2 border rounded bg-white overflow-hidden"></div>
                <div id="selectedSeatInfo" class="mt-3 text-lg font-semibold text-center text-gray-700">
                    Izvēlētā vieta: Nav izvēlēta
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button id="confirmSeatBtn" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Apstiprināt</button>
                    <button id="cancelSeatBtn" class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">Atcelt</button>
                </div>
                <div id="purchaseConfirmBox" class="hidden absolute inset-0 bg-white/95 flex flex-col items-center justify-center p-6 rounded">
                    <p class="text-lg font-bold mb-4">Vai tiešām vēlies iegādāties šo vietu?</p>
                    <p id="confirmSeatText" class="mb-4 text-gray-700"></p>
                    <div class="flex gap-4">
                        <button id="finalizePurchaseBtn" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Jā, pirkt</button>
                        <button id="cancelPurchaseBtn" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Atcelt</button>
                    </div>
                </div>
            </div>
        </div>

        <meta name="csrf-token" content="{{ csrf_token() }}">
    </div>

    {{-- CSS + JS --}}
    <link rel="stylesheet" href="{{ asset('css/seatMap.css') }}">
    <script src="{{ asset('js/seatMap.js') }}"></script>
    <script src="{{ asset('js/seatModalHandlers.js') }}"></script>
    <script src="{{ asset('js/matchPurchase.js') }}"></script>
</x-app-layout>
