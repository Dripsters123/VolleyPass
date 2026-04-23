<x-app-layout>
<div class="max-w-3xl mx-auto px-4 py-8">

    {{-- Coin balance card --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-gray-900 to-blue-900 text-white p-6 mb-8 shadow-lg">
        <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full bg-white/5"></div>
        <div class="relative z-10 flex items-center gap-5">
            <div class="flex-shrink-0 w-16 h-16 rounded-2xl bg-gradient-to-br from-orange-400 to-yellow-300 flex items-center justify-center shadow-lg">
                <svg viewBox="0 0 32 32" class="w-9 h-9" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="16" cy="16" r="14" fill="#f59e0b" stroke="#d97706" stroke-width="1.5"/>
                    <circle cx="16" cy="16" r="11" fill="none" stroke="#fde68a" stroke-width="1"/>
                    <text x="16" y="21" text-anchor="middle" font-size="13" font-weight="bold" fill="#7c3aed" font-family="serif">V</text>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-blue-200 mb-1">Tavs atlikums</p>
                <div class="text-4xl font-bold tracking-tight" id="coins">{{ intval($wallet->coins ?? $wallet->balance) }}</div>
                <div class="text-sm text-blue-200">VolleyCoins</div>
            </div>
        </div>
    </div>

    {{-- Available discount cards --}}
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-1">Pieejamās atlaižu kartes</h2>
        <p class="text-sm text-gray-500 mb-4">Izmanto kodu biļešu iegādē. Katrs kods ir vienreizējs.</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($costMap as $percent => $cost)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        {{-- Discount badge --}}
                        <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-gradient-to-br from-orange-400 to-yellow-300 flex flex-col items-center justify-center shadow">
                            <span class="text-lg font-extrabold text-white leading-none">{{ $percent }}%</span>
                            <span class="text-[9px] font-semibold text-orange-100 uppercase tracking-wide">OFF</span>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">{{ $percent }}% atlaide</div>
                            <div class="flex items-center gap-1 text-sm text-gray-500 mt-0.5">
                                <svg viewBox="0 0 20 20" class="w-3.5 h-3.5 text-yellow-500" fill="currentColor"><circle cx="10" cy="10" r="9"/><text x="10" y="14" text-anchor="middle" font-size="9" fill="white" font-weight="bold">V</text></svg>
                                <span><strong class="text-gray-800">{{ $cost }}</strong> monētas</span>
                            </div>
                        </div>
                    </div>
                    <button
                        class="buy-discount-btn flex-shrink-0 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl shadow-sm transition"
                        data-percent="{{ $percent }}">
                        Pirkt
                    </button>
                </div>
            @endforeach
        </div>
    </div>

    {{-- My discount cards --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6">
        <h3 class="font-semibold text-gray-900 mb-4">Manas atlaižu kartes</h3>
        <div id="my-cards-list" class="space-y-2">
            @forelse($cards as $card)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br {{ $card->active ? 'from-orange-400 to-yellow-300' : 'from-gray-300 to-gray-400' }} flex items-center justify-center flex-shrink-0 shadow-sm">
                            <span class="text-xs font-extrabold text-white">{{ $card->discount_percent }}%</span>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-800">
                                {{ $card->discount_percent }}% atlaide
                                <span class="ml-2 text-xs {{ $card->active ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ $card->active ? '● Aktīva' : '✓ Izmantota' }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-400 font-mono">{{ $card->code }}</div>
                        </div>
                    </div>
                    @if($card->active)
                        <button class="copy-code-btn px-3 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-medium rounded-lg transition"
                                data-code="{{ $card->code }}">
                            Kopēt
                        </button>
                    @endif
                </div>
            @empty
                <div class="text-sm text-gray-400 text-center py-6">Tev vēl nav nevienas atlaižu kartes.</div>
            @endforelse
        </div>
    </div>

    {{-- Info note --}}
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
        <strong class="block mb-1">Kā izmantot?</strong>
        Atlaižu kodu ievadi biļešu norēķinu ekrānā. Vienā pirkumā var izmantot tikai vienu kodu.
    </div>

    {{-- Purchase result --}}
    <div id="purchase-result" class="hidden mt-5 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800">
        <strong class="block mb-2">Veiksmīgi iegādāts!</strong>
        <div class="text-sm">Tavs kods: <span id="purchased-code" class="font-mono text-base font-bold"></span>
            <button id="copy-purchased-code" class="ml-3 px-3 py-1 border border-green-300 rounded-lg text-xs hover:bg-green-100 transition">Kopēt</button>
        </div>
    </div>
    <div id="error-box" class="hidden mt-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm"></div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buyButtons = document.querySelectorAll('.buy-discount-btn');
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const walletBalanceEl = document.getElementById('coins');
    const resultBox = document.getElementById('purchase-result');
    const purchasedCodeEl = document.getElementById('purchased-code');
    const errorBox = document.getElementById('error-box');
    const myCardsList = document.getElementById('my-cards-list');

    function showError(msg) {
        errorBox.textContent = msg;
        errorBox.classList.remove('hidden');
    }

    function hideError() {
        errorBox.classList.add('hidden');
        errorBox.textContent = '';
    }

    async function buy(percent) {
        hideError();
        try {
            const res = await fetch("{{ route('wallet.buy.post') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ discount_percent: percent })
            });

            const data = await res.json();

            if (!res.ok) {
                let err = (data && data.error) ? data.error : 'Failed to buy discount';
                if (typeof err === 'object') err = JSON.stringify(err);
                showError(err);
                return;
            }

            const card = data.discount_card;
            const costMap = @json($costMap);
            const cost = costMap[card.discount_percent] || 0;
            let cur = parseInt(walletBalanceEl.textContent || '0', 10);
            walletBalanceEl.textContent = Math.max(0, cur - cost);

            const node = document.createElement('div');
            node.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100';
            node.innerHTML = `
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-orange-400 to-yellow-300 flex items-center justify-center shadow-sm">
                        <span class="text-xs font-extrabold text-white">${card.discount_percent}%</span>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-800">${card.discount_percent}% atlaide <span class="ml-2 text-xs text-green-600">● Aktīva</span></div>
                        <div class="text-xs text-gray-400 font-mono">${card.code}</div>
                    </div>
                </div>
                <button class="copy-code-btn px-3 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-medium rounded-lg transition" data-code="${card.code}">Kopēt</button>`;

            // Remove empty state if present
            const empty = myCardsList.querySelector('.text-center');
            if (empty) empty.remove();
            myCardsList.prepend(node);

            purchasedCodeEl.textContent = card.code;
            resultBox.classList.remove('hidden');
            resultBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        } catch (err) {
            showError(err.message || 'Network error');
        }
    }

    buyButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const percent = parseInt(this.dataset.percent, 10);
            if (!confirm(`Iegādāties ${percent}% atlaižu karti?`)) return;
            buy(percent);
        });
    });

    document.body.addEventListener('click', function (e) {
        if (e.target.matches('.copy-code-btn') || e.target.matches('#copy-purchased-code')) {
            const code = e.target.dataset.code || purchasedCodeEl.textContent;
            if (!code) return;
            navigator.clipboard?.writeText(code).then(() => {
                e.target.textContent = 'Kopēts!';
                setTimeout(() => { e.target.textContent = 'Kopēt'; }, 2000);
            }).catch(() => {
                prompt('Kopē šo kodu:', code);
            });
        }
    });
});
</script>
</x-app-layout>