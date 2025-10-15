<x-app-layout>
<div class="container p-6">
  <h1 class="text-2xl mb-4">My VolleyCoins</h1>

  <div class="mb-4">
    <div class="text-4xl font-bold">{{ number_format($wallet->balance, 2) }} ⚪</div>
    <p class="text-sm text-gray-600">VolleyCoins can be redeemed for discounts or traded in the marketplace.</p>
  </div>

  <h2 class="text-lg mb-2">Recent activity</h2>
  <div class="border rounded p-3">
    @forelse($transactions as $t)
      <div class="flex justify-between py-2 border-b">
        <div>
          <div class="font-semibold">{{ $t->type }}</div>
          <div class="text-sm text-gray-600">{{ $t->note ?? '' }}</div>
        </div>
        <div class="text-right">
          <div class="font-medium">{{ number_format($t->amount,2) }}</div>
          <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($t->created_at)->diffForHumans() }}</div>
        </div>
      </div>
    @empty
      <div class="text-sm text-gray-600">No wallet activity yet.</div>
    @endforelse
  </div>
</div>
</x-app-layout>
