<x-app-layout>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Admin iesūtne</h1>
            <p class="text-sm text-gray-500 mt-0.5">Pārvaldiet mača, rezultātu un produktu pieprasījumus</p>
        </div>
    </div>

    {{-- Modern filter bar --}}
    <div x-data="inboxFilters()">
        <form method="GET" id="filter-form">
            {{-- Search --}}
            <div class="relative mb-4">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 0 5 11a6 6 0 0 0 12 0z"/>
                    </svg>
                </div>
                <input type="text" name="user" value="{{ request('user') }}"
                    placeholder="Meklēt pēc lietotāja vārda…"
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            </div>

            {{-- Type chips --}}
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mr-1">Tips:</span>
                <button type="button" @click="setType('')"
                    :class="activeType === '' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-400'"
                    class="px-3.5 py-1.5 rounded-full text-sm font-medium border transition-all">Visi</button>
                <button type="button" @click="setType('match')"
                    :class="activeType === 'match' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-400'"
                    class="px-3.5 py-1.5 rounded-full text-sm font-medium border transition-all">🏐 Mači</button>
                <button type="button" @click="setType('score_update')"
                    :class="activeType === 'score_update' ? 'bg-orange-500 text-white border-orange-500' : 'bg-white text-gray-600 border-gray-200 hover:border-orange-400'"
                    class="px-3.5 py-1.5 rounded-full text-sm font-medium border transition-all">📊 Rezultāti</button>
                <button type="button" @click="setType('product')"
                    :class="activeType === 'product' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-gray-600 border-gray-200 hover:border-emerald-400'"
                    class="px-3.5 py-1.5 rounded-full text-sm font-medium border transition-all">🛒 Produkti</button>
                <input type="hidden" name="type" :value="activeType">
            </div>

            {{-- Status chips --}}
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mr-1">Statuss:</span>
                <button type="button" @click="setStatus('')"
                    :class="activeStatus === '' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-400'"
                    class="px-3.5 py-1.5 rounded-full text-sm font-medium border transition-all">Visi</button>
                <button type="button" @click="setStatus('pending')"
                    :class="activeStatus === 'pending' ? 'bg-yellow-500 text-white border-yellow-500' : 'bg-white text-gray-600 border-gray-200 hover:border-yellow-400'"
                    class="px-3.5 py-1.5 rounded-full text-sm font-medium border transition-all">⏳ Gaida</button>
                <button type="button" @click="setStatus('accepted')"
                    :class="activeStatus === 'accepted' ? 'bg-green-600 text-white border-green-600' : 'bg-white text-gray-600 border-gray-200 hover:border-green-400'"
                    class="px-3.5 py-1.5 rounded-full text-sm font-medium border transition-all">✅ Apstiprināti</button>
                <button type="button" @click="setStatus('rejected')"
                    :class="activeStatus === 'rejected' ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-600 border-gray-200 hover:border-red-400'"
                    class="px-3.5 py-1.5 rounded-full text-sm font-medium border transition-all">❌ Noraidīti</button>
                <input type="hidden" name="status" :value="activeStatus">
            </div>

            {{-- Date range & submit --}}
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">No datuma</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                        class="px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Līdz datumam</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                        class="px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition">
                    Filtrēt
                </button>
                @if(request()->hasAny(['type','status','user','start_date','end_date']))
                    <a href="{{ route('admin.match_requests.inbox') }}"
                        class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 text-sm font-medium rounded-lg transition">
                        Notīrīt filtrus
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Desktop table --}}
    <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-6">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tips</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Sūtītājs</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Uzskaite</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Datums</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Statuss</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Darbības</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($requests as $item)
                    @php
                        $rawDate = $item->created_at ?? ($item->start_time ?? null);
                        $displayDate = null;
                        if ($rawDate) {
                            if ($rawDate instanceof \DateTimeInterface) {
                                $displayDate = \Carbon\Carbon::instance($rawDate)->timezone('Europe/Riga');
                            } else {
                                try { $displayDate = \Carbon\Carbon::parse($rawDate)->timezone('Europe/Riga'); } catch (\Throwable $e) {}
                            }
                        }
                        $typeLabel = match($item->inbox_type ?? '') {
                            'match' => ['label' => 'Mačs', 'class' => 'bg-blue-100 text-blue-700'],
                            'score_update' => ['label' => 'Rezultāts', 'class' => 'bg-orange-100 text-orange-700'],
                            'product' => ['label' => 'Produkts', 'class' => 'bg-emerald-100 text-emerald-700'],
                            default => ['label' => ucfirst($item->inbox_type ?? 'request'), 'class' => 'bg-gray-100 text-gray-600'],
                        };
                    @endphp
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeLabel['class'] }}">
                                {{ $typeLabel['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-medium text-gray-800">{{ optional($item->user)->name ?? '—' }}</div>
                            @if(optional($item->user)->email)
                                <div class="text-xs text-gray-400">{{ optional($item->user)->email }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 max-w-xs">
                            @if(($item->inbox_type ?? null) === 'product')
                                <div class="font-medium text-gray-800">{{ $item->product_name ?? "Produkts #{$item->id}" }}</div>
                                <div class="text-xs text-gray-500 mt-0.5 truncate">{{ \Illuminate\Support\Str::limit($item->notes ?? $item->description ?? '', 90) }}</div>
                            @else
                                <div class="font-medium text-gray-800">
                                    {{ $item->home_team ?? ($item->home_team_name ?? 'Home') }}
                                    <span class="text-gray-400 mx-1">vs</span>
                                    {{ $item->away_team ?? ($item->away_team_name ?? 'Away') }}
                                </div>
                                @if(isset($item->start_time))
                                    <div class="text-xs text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($item->start_time)->timezone('Europe/Riga')->format('d.m.Y H:i') }}</div>
                                @endif
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center text-sm text-gray-500">
                            {{ $displayDate ? $displayDate->format('d.m.Y H:i') : '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                {{ ($item->status ?? '') === 'pending'  ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ ($item->status ?? '') === 'accepted' ? 'bg-green-100 text-green-700' : '' }}
                                {{ ($item->status ?? '') === 'rejected' ? 'bg-red-100 text-red-700' : '' }}">
                                {{ ucfirst($item->status ?? 'pending') }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if(($item->inbox_type ?? '') === 'product')
                                <a href="{{ route('admin.product_requests.show', $item->id) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition">
                                    Skatīt →
                                </a>
                            @else
                                <a href="{{ route('admin.match_requests.show', $item->id) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition">
                                    Skatīt →
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                            <div class="text-4xl mb-3">📭</div>
                            <div class="font-medium">Nav pieprasījumu</div>
                            <div class="text-sm mt-1">Mēģiniet mainīt filtrus</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $requests->links() }}
        </div>
    </div>

    {{-- Mobile cards --}}
    <div class="md:hidden mt-6 space-y-3">
        @forelse($requests as $item)
            @php
                $rawDate = $item->created_at ?? ($item->start_time ?? null);
                $displayDate = null;
                if ($rawDate) {
                    if ($rawDate instanceof \DateTimeInterface) {
                        $displayDate = \Carbon\Carbon::instance($rawDate)->timezone('Europe/Riga');
                    } else {
                        try { $displayDate = \Carbon\Carbon::parse($rawDate)->timezone('Europe/Riga'); } catch (\Throwable $e) {}
                    }
                }
            @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                <div class="flex justify-between items-start mb-2">
                    <div class="font-semibold text-gray-800">{{ ucfirst($item->inbox_type ?? 'request') }}</div>
                    <div class="text-xs text-gray-400">{{ $displayDate ? $displayDate->format('d.m.Y H:i') : '—' }}</div>
                </div>
                <div class="text-sm text-gray-600 mb-1">{{ optional($item->user)->name ?? '—' }}</div>
                @if(($item->inbox_type ?? '') === 'product')
                    <div class="font-medium text-sm">{{ $item->product_name ?? "Produkts #{$item->id}" }}</div>
                @else
                    <div class="font-medium text-sm">{{ $item->home_team ?? 'Home' }} vs {{ $item->away_team ?? 'Away' }}</div>
                @endif
                <div class="mt-3 flex gap-2 items-center justify-between">
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ ($item->status ?? '') === 'pending'  ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ ($item->status ?? '') === 'accepted' ? 'bg-green-100 text-green-700' : '' }}
                        {{ ($item->status ?? '') === 'rejected' ? 'bg-red-100 text-red-700' : '' }}">
                        {{ ucfirst($item->status ?? 'pending') }}
                    </span>
                    @if(($item->inbox_type ?? '') === 'product')
                        <a href="{{ route('admin.product_requests.show', $item->id) }}" class="text-sm text-blue-600 font-medium hover:underline">Skatīt →</a>
                    @else
                        <a href="{{ route('admin.match_requests.show', $item->id) }}" class="text-sm text-blue-600 font-medium hover:underline">Skatīt →</a>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center text-gray-400 py-12">
                <div class="text-4xl mb-3">📭</div>
                <div>Nav pieprasījumu</div>
            </div>
        @endforelse
        <div class="pt-2">{{ $requests->links() }}</div>
    </div>

</div>

<script>
function inboxFilters() {
    return {
        activeType: '{{ request("type", "") }}',
        activeStatus: '{{ request("status", "") }}',
        setType(t) { this.activeType = t; },
        setStatus(s) { this.activeStatus = s; },
    };
}
</script>
</x-app-layout>