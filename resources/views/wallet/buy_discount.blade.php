<x-app-layout>
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Nopirkt atlaižu karti</h1>

    <div id="wallet-panel" class="mb-6 p-4 rounded shadow-sm bg-white">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm text-gray-500">Your coins</div>
                <div id="coins" class="text-3xl font-semibold">{{ intval($wallet->coins) }}</div>
            </div>
        </div>
    </div>

    <div class="mb-6">
        <h2 class="text-xl font-semibold mb-2">Pieejamās atlaižu kartes</h2>
        <p class="text-sm text-gray-600 mb-4">
            Izmanto atlaižu kodu, kad veic norēķinus par biļetēm (checkout). Katrs kods ir vienreizējs.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($costMap as $percent => $cost)
            <div class="p-4 border rounded bg-white">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <div class="text-lg font-semibold">{{ $percent }}% off</div>
                        <div class="text-sm text-gray-600">Cost: <strong>{{ $cost }}</strong> coins</div>
                    </div>
                    <div>
                        <button
                            class="buy-discount-btn px-4 py-2 rounded shadow-sm bg-blue-600 text-white hover:bg-blue-700"
                            data-percent="{{ $percent }}"
                        >
                            Buy
                        </button>
                    </div>
                </div>

                <div class="text-sm text-gray-700">
                    Šī atlaide attiecas uz Maču biļešu cenām, veicot pasūtījumu (checkout).
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="mb-6">
        <h3 class="text-lg font-semibold mb-2">Manas atlaižu kartes</h3>
        <div id="my-cards-list" class="space-y-2">
            @forelse($cards as $card)
                <div class="p-3 border rounded bg-gray-50 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium">{{ $card->discount_percent }}% — <span class="text-xs text-gray-500">{{ $card->active ? 'Active' : 'Used' }}</span></div>
                        <div class="text-xs text-gray-600">Code: <span class="font-mono">{{ $card->code }}</span></div>
                    </div>
                    <div>
                        @if($card->active)
                            <button class="copy-code-btn px-3 py-1 text-sm rounded border" data-code="{{ $card->code }}">Copy</button>
                        @else
                            <span class="text-xs text-gray-500">Used</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-3 text-sm text-gray-600">You don't have any discount cards yet.</div>
            @endforelse
        </div>
    </div>

    <div class="mb-6">
        <div class="p-4 bg-yellow-50 border rounded">
            <strong class="block mb-1">Svarīgi</strong>
            <div class="text-sm text-gray-700">
                
Atlaižu kodus šeit nevar ievadīt. Kad turpināt biļešu iegādi, ievadiet savu atlaižu kodu norēķinu ekrānā.
Vienā pirkumā var izmantot tikai vienu atlaižu kodu (kodu atkārtošana nav atļauta).
            </div>
        </div>
    </div>

    <div id="purchase-result" class="hidden p-4 border-l-4 border-green-500 bg-green-50 rounded mb-4">
        <div class="mb-2">
            <strong>Purchased!</strong>
        </div>
        <div>
            Your discount code: <span id="purchased-code" class="font-mono text-lg"></span>
            <button id="copy-purchased-code" class="ml-2 px-2 py-1 border rounded text-sm">Copy</button>
        </div>
    </div>
    <div id="error-box" class="hidden mt-4 p-3 border-l-4 border-red-500 bg-red-50 text-red-700 rounded"></div>
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

            const cost = @json($costMap)[card.discount_percent] || 0;
            let cur = parseInt(walletBalanceEl.textContent || '0', 10);
            walletBalanceEl.textContent = Math.max(0, cur - cost);

            const node = document.createElement('div');
            node.className = 'p-3 border rounded bg-gray-50 flex items-center justify-between';
            node.innerHTML = `<div>
                    <div class="text-sm font-medium">${card.discount_percent}% — <span class="text-xs text-gray-500">Active</span></div>
                    <div class="text-xs text-gray-600">Code: <span class="font-mono">${card.code}</span></div>
                </div>
                <div><button class="copy-code-btn px-3 py-1 text-sm rounded border" data-code="${card.code}">Copy</button></div>`;
            if (myCardsList) myCardsList.prepend(node);

            purchasedCodeEl.textContent = card.code;
            resultBox.classList.remove('hidden');

        } catch (err) {
            showError(err.message || 'Network error');
            console.error(err);
        }
    }

    buyButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const percent = this.dataset.percent;
            if (!confirm(`Buy ${percent}% discount card? This will cost coins.`)) return;
            buy(parseInt(percent, 10));
        });
    });

    document.body.addEventListener('click', function (e) {
        if (e.target.matches('.copy-code-btn') || e.target.matches('#copy-purchased-code')) {
            const code = e.target.dataset.code || purchasedCodeEl.textContent;
            if (!code) return;
            navigator.clipboard?.writeText(code).then(() => {
                alert('Code copied to clipboard');
            }).catch(() => {
                prompt('Copy this code:', code);
            });
        }
    });
});
</script>
</x-app-layout>
