<x-app-layout>
<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold mb-6">Pagājušās spēles detaļas</h1>

    <div class="bg-white p-6 rounded shadow space-y-6">

        {{-- Match Header --}}
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <strong>{{ $match->home_team_name }}</strong>
                <span class="text-gray-500">vs</span>
                <strong>{{ $match->away_team_name }}</strong>
            </div>
            <span class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($match->start_time)->format('d M Y, H:i') }}</span>
        </div>

        {{-- Final Score --}}
        <div class="text-xl font-semibold text-center">
            Gala rezultāts: {{ $match->home_score }} - {{ $match->away_score }}
        </div>

        {{-- Arena --}}
        @if($match->arena)
        <div class="text-sm text-gray-700">
            Arēna: {{ $match->arena['name'] ?? 'N/A' }} - {{ $match->arena['city'] ?? '' }}, {{ $match->arena['country_name'] ?? '' }}
        </div>
        @endif

        {{-- Legend --}}
        <div class="mt-4 text-sm text-gray-600">
            <strong>Krāsu nozīme:</strong>
            <div class="flex gap-4 mt-1">
                <span class="flex items-center gap-1"><span class="w-4 h-4 bg-blue-400 inline-block rounded"></span> Mājinieki</span>
                <span class="flex items-center gap-1"><span class="w-4 h-4 bg-red-400 inline-block rounded"></span> Viesi</span>
            </div>
        </div>

        {{-- Statistics --}}
        <div class="mt-4">
            <h2 class="text-lg font-bold mb-2">Spēles statistika</h2>

            @php
                // Group statistics by type
                $groupedStats = $match->statistics->groupBy('type');

                // Translation arrays
                $translateType = [
                    'Aces' => 'Aces',
                    'Points won' => 'Iegūtie punkti',
                    'Receiver points won' => 'Saņēmēja punkti',
                    'Timeouts' => 'Pauzes',
                    'Max points in a row' => 'Maksimālie punkti pēc kārtas',
                    'Service errors' => 'Servisa kļūdas',
                    'Service points won' => 'Servisa iegūtie punkti',
                ];

                $translateCategory = [
                    'Attacking' => 'Uzbrukums',
                    'Serving' => 'Serviss',
                    'Defending' => 'Aizsardzība',
                ];
            @endphp

            @foreach($groupedStats as $type => $stats)
                <div class="border rounded mb-2">
                    {{-- Type Header --}}
                    <button type="button" class="w-full text-left px-4 py-2 bg-gray-100 hover:bg-gray-200 flex justify-between items-center type-toggle">
                        <span>{{ $translateType[$type] ?? $type }}</span>
                        <svg class="w-4 h-4 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Statistics Table --}}
                    <div class="type-content hidden p-2 overflow-x-auto">
                        <table class="table-auto w-full border text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="border px-2 py-1">Periods</th>
                                    <th class="border px-2 py-1">Kategorija</th>
                                    <th class="border px-2 py-1 text-center">Mājinieki</th>
                                    <th class="border px-2 py-1 text-center">Viesi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats as $stat)
                                    @php
                                        $homeValue = is_numeric($stat->home_team) ? (int)$stat->home_team : 0;
                                        $awayValue = is_numeric($stat->away_team) ? (int)$stat->away_team : 0;
                                        $maxValue = max($homeValue, $awayValue, 1);
                                        $homeWidth = ($homeValue / $maxValue) * 100;
                                        $awayWidth = ($awayValue / $maxValue) * 100;
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="border px-2 py-1">{{ $stat->period }}</td>
                                        <td class="border px-2 py-1">{{ $translateCategory[$stat->category] ?? $stat->category }}</td>
                                        <td class="border px-2 py-1 text-center">
                                            <div class="bg-blue-400 h-4 rounded mb-1" style="width: {{ $homeWidth }}%"></div>
                                            <span class="text-xs">{{ $homeValue }}</span>
                                        </td>
                                        <td class="border px-2 py-1 text-center">
                                            <div class="bg-red-400 h-4 rounded mb-1" style="width: {{ $awayWidth }}%"></div>
                                            <span class="text-xs">{{ $awayValue }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>

{{-- Scripts for collapsible --}}
<script>
    document.querySelectorAll('.type-toggle').forEach(button => {
        button.addEventListener('click', () => {
            const content = button.nextElementSibling;
            content.classList.toggle('hidden');
            const icon = button.querySelector('svg');
            icon.classList.toggle('rotate-180');
        });
    });

    // Mobile: collapse all by default
    if(window.innerWidth < 768) {
        document.querySelectorAll('.type-content').forEach(c => c.classList.add('hidden'));
    }
</script>
</x-app-layout>
