<x-app-layout>
<div class="max-w-3xl mx-auto px-4 py-8">

    {{-- Coin balance card --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-gray-900 to-blue-900 text-white p-6 mb-8 shadow-lg">
        <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full bg-white/5"></div>
        <div class="absolute top-6 right-6 w-20 h-20 rounded-full bg-orange-400/10"></div>
        <div class="relative z-10">
            <p class="text-xs font-semibold uppercase tracking-widest text-blue-200 mb-3">Mana VolleyCoins bilance</p>
            <div class="flex items-center gap-4 mb-4">
                {{-- Coin icon badge --}}
                <div class="flex-shrink-0 w-16 h-16 rounded-2xl bg-gradient-to-br from-orange-400 to-yellow-300 flex items-center justify-center shadow-lg">
                    <svg viewBox="0 0 32 32" class="w-9 h-9" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="16" cy="16" r="14" fill="#f59e0b" stroke="#d97706" stroke-width="1.5"/>
                        <circle cx="16" cy="16" r="11" fill="none" stroke="#fde68a" stroke-width="1"/>
                        <text x="16" y="21" text-anchor="middle" font-size="13" font-weight="bold" fill="#7c3aed" font-family="serif">V</text>
                    </svg>
                </div>
                <div>
                    <div class="text-4xl font-bold tracking-tight">{{ number_format($wallet->balance, 0) }}</div>
                    <div class="text-sm text-blue-200">VolleyCoins</div>
                </div>
            </div>
            <p class="text-xs text-blue-200/80">Izmanto monētas, lai iegādātos atlaižu kartes biļešu iegādei.</p>
        </div>
    </div>

    {{-- Recent activity --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-8">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Pēdējie darījumi</h2>
        <div class="space-y-2">
            @forelse($transactions as $t)
                <div class="flex items-center justify-between py-2.5 border-b border-gray-50 last:border-0">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-800">{{ $t->type }}</div>
                            <div class="text-xs text-gray-400">{{ $t->note ?? '' }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold {{ $t->amount >= 0 ? 'text-green-600' : 'text-red-500' }}">
                            {{ $t->amount >= 0 ? '+' : '' }}{{ number_format($t->amount, 0) }} VC
                        </div>
                        <div class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($t->created_at)->diffForHumans() }}</div>
                    </div>
                </div>
            @empty
                <div class="text-sm text-gray-400 text-center py-6">Nav darījumu vēl.</div>
            @endforelse
        </div>
    </div>

    {{-- Discount cards link --}}
    <div class="text-center">
        <a href="{{ route('wallet.cards.list') }}"
           class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl shadow-sm transition text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
            </svg>
            Iegādāties atlaižu kartes
        </a>
    </div>

</div>
</x-app-layout>
