<x-app-layout>
<div class="container p-6">
  <h1 class="text-2xl font-bold mb-3">Order cancelled</h1>
  <p>Your product checkout was cancelled. Your order remains in a pending state until you try again.</p>
  <div class="mt-4">
    <a href="{{ route('products.index') }}" class="btn btn-primary">Back to store</a>
  </div>
</div>
</x-app-layout>
