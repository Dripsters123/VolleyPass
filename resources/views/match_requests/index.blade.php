<x-app-layout>
<div class="max-w-5xl mx-auto px-4 py-8" x-data="{ tab: '{{ request('tab', 'active') }}' }">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mani pieprasījumi</h1>
            <p class="text-sm text-gray-500 mt-0.5">Pārvaldiet savus mača un produktu pieprasījumus</p>
        </div>
        <a href="{{ route('match_requests.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Jauns pieprasījums
        </a>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex gap-6">
            <button @click="tab = 'active'"
                :class="tab === 'active' ? 'border-blue-600 text-blue-700 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="py-3 border-b-2 text-sm transition-colors whitespace-nowrap">
                Aktīvie
                @php $activeCount = $requests->filter(fn($r) => in_array($r->status, ['pending', 'reviewing']))->count(); @endphp
                @if($activeCount > 0)
                    <span class="ml-1.5 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">{{ $activeCount }}</span>
                @endif
            </button>
            <button @click="tab = 'history'"
                :class="tab === 'history' ? 'border-blue-600 text-blue-700 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="py-3 border-b-2 text-sm transition-colors whitespace-nowrap">
                Vēsture
                @php $historyCount = $requests->filter(fn($r) => in_array($r->status, ['accepted', 'rejected', 'appealed']))->count(); @endphp
                @if($historyCount > 0)
                    <span class="ml-1.5 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">{{ $historyCount }}</span>
                @endif
            </button>
        </nav>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    {{-- Active tab --}}
    <div x-show="tab === 'active'" x-cloak>
        @php $activeRequests = $requests->filter(fn($r) => in_array($r->status, ['pending', 'reviewing'])); @endphp
        @if($activeRequests->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <svg class="w-14 h-14 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <div class="font-medium text-gray-500">Nav aktīvu pieprasījumu</div>
                <div class="text-sm mt-1">Izveidojiet savu pirmo pieprasījumu!</div>
            </div>
        @else
            <div class="space-y-3">
                @foreach($activeRequests as $r)
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $r->status === 'reviewing' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $r->status === 'reviewing' ? 'Tiek izskatīts' : 'Gaida' }}</span>
                                @if($r->type === 'match')
                                    <span class="text-xs text-gray-400">Mača pieprasījums</span>
                                @elseif($r->type === 'score_update')
                                    <span class="text-xs text-gray-400">Rezultāta pieprasījums</span>
                                @elseif($r->type === 'product')
                                    <span class="text-xs text-gray-400">Produkta pieprasījums</span>
                                @endif
                            </div>
                            @if(in_array($r->type, ['match', 'score_update']))
                                <div class="font-semibold text-gray-900">{{ $r->home_team }} vs {{ $r->away_team }}</div>
                                <div class="text-sm text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($r->start_time)->format('d.m.Y H:i') }}</div>
                            @elseif($r->type === 'product')
                                <div class="font-semibold text-gray-900">{{ $r->title }}</div>
                                <div class="text-sm text-gray-500 mt-0.5 truncate">{{ \Illuminate\Support\Str::limit($r->description ?? '', 80) }}</div>
                            @endif
                            <div class="text-xs text-gray-400 mt-1">{{ $r->created_at?->diffForHumans() }}</div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            @if(in_array($r->type, ['match', 'score_update']))
                                <a href="{{ route('match_requests.view', $r->id) }}"
                                   class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition">Skatīt</a>
                                <a href="{{ route('match_requests.edit', $r->id) }}"
                                   class="px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-medium rounded-lg transition">Rediģēt</a>
                                <form action="{{ route('match_requests.cancel', $r->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            onclick="vpConfirm('Atcelt šo pieprasījumu?', () => this.closest('form').submit(), { danger: true, confirmText: 'Atcelt' })"
                                            class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded-lg transition">Atcelt</button>
                                </form>
                            @elseif($r->type === 'product')
                                <a href="{{ route('product_requests.edit', $r->id) }}"
                                   class="px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-medium rounded-lg transition">Rediģēt</a>
                                <form action="{{ route('product_requests.cancel', $r->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            onclick="vpConfirm('Atcelt šo pieprasījumu?', () => this.closest('form').submit(), { danger: true, confirmText: 'Atcelt' })"
                                            class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded-lg transition">Atcelt</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- History tab --}}
    <div x-show="tab === 'history'" x-cloak>
        @php $historyRequests = $requests->filter(fn($r) => in_array($r->status, ['accepted', 'rejected', 'appealed'])); @endphp
        @if($historyRequests->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <svg class="w-14 h-14 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="font-medium text-gray-500">Vēsture tukša</div>
                <div class="text-sm mt-1">Apstrādātie pieprasījumi parādīsies šeit</div>
            </div>
        @else
            <div class="space-y-3">
                @foreach($historyRequests as $r)
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-start justify-between gap-4 opacity-90">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $r->status === 'accepted' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $r->status === 'rejected' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $r->status === 'appealed' ? 'bg-purple-100 text-purple-700' : '' }}">
                                    @php $hl = ['accepted'=>'Apstiprināts','rejected'=>'Noraidīts','appealed'=>'Apelācija nosūtīta']; @endphp
                                    {{ $hl[$r->status] ?? ucfirst($r->status) }}
                                </span>
                                @if($r->type === 'match')
                                    <span class="text-xs text-gray-400">Mača pieprasījums</span>
                                @elseif($r->type === 'score_update')
                                    <span class="text-xs text-gray-400">Rezultāta pieprasījums</span>
                                @elseif($r->type === 'product')
                                    <span class="text-xs text-gray-400">Produkta pieprasījums</span>
                                @endif
                            </div>
                            @if(in_array($r->type, ['match', 'score_update']))
                                <div class="font-semibold text-gray-900">{{ $r->home_team }} vs {{ $r->away_team }}</div>
                                <div class="text-sm text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($r->start_time)->format('d.m.Y H:i') }}</div>
                            @elseif($r->type === 'product')
                                <div class="font-semibold text-gray-900">{{ $r->title }}</div>
                                <div class="text-sm text-gray-500 mt-0.5 truncate">{{ \Illuminate\Support\Str::limit($r->description ?? '', 80) }}</div>
                            @endif
                            <div class="text-xs text-gray-400 mt-1">{{ $r->updated_at?->diffForHumans() }}</div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            @if(in_array($r->type, ['match', 'score_update']))
                                <a href="{{ route('match_requests.view', $r->id) }}"
                                   class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium rounded-lg transition">Skatīt</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="mt-6">{{ $requests->links() }}</div>
</div>

<style>[x-cloak]{display:none!important}</style>
</x-app-layout>
