<x-app-layout>
<div class="container p-6">
  <h1 class="text-2xl font-bold mb-3">Thanks — payment received</h1>
  <p>Stripe completed your payment. We’re processing your order — you’ll get an update when it ships.</p>
  <p class="text-sm text-gray-600 mt-2">Note: this page is informational. The server webhook is the real confirmation.</p>

  <div class="mt-4">
    <a href="{{ route('products.index') }}" class="btn btn-primary">Continue shopping</a>
    <a href="{{ route('dashboard') }}" class="btn btn-secondary ml-2">My dashboard</a>
  </div>
</div>
</x-app-layout>
