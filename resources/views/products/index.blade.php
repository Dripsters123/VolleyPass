<x-app-layout>
<div class="max-w-7xl mx-auto px-6 py-8">
  <h1 class="text-3xl font-bold mb-6 text-gray-800">Preces</h1>

  @if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-2 rounded-md">{{ session('success') }}</div>
  @endif

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($products as $product)
      <div class="bg-white shadow-md rounded-2xl overflow-hidden transition-transform hover:-translate-y-1 hover:shadow-lg">
        @if($product->image_path)
          <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->title }}" class="h-48 w-full object-cover">
        @endif
        <div class="p-4 flex flex-col">
          <h3 class="text-lg font-semibold text-gray-800 mb-1">{{ $product->title }}</h3>
          <p class="text-sm text-gray-600 flex-grow">{{ Str::limit($product->description, 100) }}</p>
          <div class="mt-4 flex items-center justify-between">
            <span class="text-xl font-bold text-blue-700">€{{ number_format($product->price,2) }}</span>
            <a href="{{ route('products.show', $product) }}" class="px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">Apskatīt</a>
          </div>
        </div>
      </div>
    @empty
      <p class="text-gray-600">Nav neviena produkta šobrīd.</p>
    @endforelse
  </div>

  <div class="mt-6">{{ $products->links() }}</div>
</div>
</x-app-layout>
