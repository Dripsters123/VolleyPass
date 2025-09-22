<x-app-layout title="VolleyPass – Mana Pārskata lapa">
    <div class="max-w-7xl mx-auto px-6 py-12">

        <!-- Header gradient -->
        <div class="mb-10 rounded-xl bg-gradient-to-r from-orange-400 to-blue-600 text-white p-8 shadow-lg">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold">Sveiks, {{ auth()->user()->name }}!</h1>
                <img src="{{ asset('images/volleyball.png') }}" alt="volleyball" class="h-16 w-auto">
            </div>
            <p class="mt-2 text-orange-100">Tavs personīgais volejbola pārskats un ieteikumi</p>
        </div>

        <!-- Grid -->
        <div class="grid lg:grid-cols-3 gap-8 items-start">

            <!-- Left: Ieteikumi -->
            <section class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Ieteikumi tev</h2>

                    <div class="grid md:grid-cols-2 gap-6">
                        @forelse($recommendations as $rec)
                            <div class="p-4 rounded-xl bg-gradient-to-r from-orange-50 to-blue-50 shadow">
                                <h3 class="text-lg font-semibold text-blue-700">
                                    {{ $rec['home_team_name'] }} vs {{ $rec['away_team_name'] }}
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    Datums: {{ \Carbon\Carbon::parse($rec['start_time'])->translatedFormat('d.m.Y H:i') }}
                                </p>
                                <a href="{{ route('volleyball.show', $rec['id']) }}"
                                   class="mt-3 inline-block px-4 py-2 bg-orange-500 text-white rounded-lg shadow hover:bg-orange-600">
                                   Skatīt spēli
                                </a>
                            </div>
                        @empty
                            <p class="text-gray-500">Nav ieteikumu – iegādājies biļetes, lai saņemtu personalizētus!</p>
                        @endforelse
                    </div>
                </div>
            </section>

            <!-- Right sidebar -->
            <aside class="space-y-6">
                <!-- Recent Purchases -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-lg font-bold text-orange-600 mb-3">Nesenie pirkumi</h2>
                    <ul class="text-sm text-gray-700 space-y-1 max-h-32 overflow-y-auto">
                        @forelse($recentPurchases as $ticket)
                            <li>
                                🎟️ {{ $ticket->event->name ?? 'Notikums' }}
                                – <span class="text-blue-600">{{ $ticket->amount_paid }}€</span>
                            </li>
                        @empty
                            <li>Nav pirkumu</li>
                        @endforelse
                    </ul>
                </div>

                <!-- Recently Viewed -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-lg font-bold text-orange-600 mb-3">Nesen apskatītās</h2>
                    <ul class="text-sm text-gray-700 space-y-1 max-h-32 overflow-y-auto">
                        @forelse($recentMatches as $matchId)
                            <li>
                                👀 <a href="{{ route('volleyball.show', $matchId) }}" class="text-blue-600 hover:underline">
                                    Spēle #{{ $matchId }}
                                </a>
                            </li>
                        @empty
                            <li>Nesen nav skatīts</li>
                        @endforelse
                    </ul>
                </div>

                <!-- Upcoming Matches -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-lg font-bold text-orange-600 mb-3">Nākošās spēles</h2>
                    <ul class="text-sm text-gray-700 space-y-1 max-h-32 overflow-y-auto">
                        @forelse($upcomingMatches as $match)
                            <li>
                                🏐 <a href="{{ route('volleyball.show', $match['id']) }}" class="text-blue-600 hover:underline">
                                    {{ $match['home_team_name'] }} vs {{ $match['away_team_name'] }}
                                </a>
                            </li>
                        @empty
                            <li>Nav gaidāmu spēļu</li>
                        @endforelse
                    </ul>
                </div>
            </aside>

        </div>
    </div>
</x-app-layout>
