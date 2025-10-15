<x-app-layout>
<div class="container mx-auto p-4 sm:p-6">
  <a href="{{ route('products.index') }}" class="text-sm text-gray-600 mb-4 inline-block">← Atpakaļ</a>

  <div class="flex flex-col md:flex-row gap-6">
    <div class="w-full md:w-1/3">
      @if($product->image_path)
        <img src="{{ asset('storage/'.$product->image_path) }}" class="w-full h-64 sm:h-96 object-cover rounded">
      @endif
    </div>

    <div class="w-full md:w-2/3 flex flex-col justify-between">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold">{{ $product->title }}</h1>
        <p class="mt-2 text-gray-700">{{ $product->description }}</p>
        <div class="mt-4 text-xl font-semibold">€{{ number_format($product->price, 2) }}</div>
      </div>

      <div class="mt-6 flex flex-col sm:flex-row gap-3">
        @auth
          <form method="POST" action="{{ route('products.buy', $product) }}" id="buyForm">
            @csrf
            <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Pirkt</button>
          </form>
        @else
          <a href="{{ route('login') }}" class="w-full sm:w-auto px-4 py-2 bg-gray-600 text-white rounded-lg text-center hover:bg-gray-700">Pieslēgties lai pirktu</a>
        @endauth
      </div>
    </div>
  </div>
</div>

@auth
<script>
document.getElementById('buyForm')?.addEventListener('submit', function(e){
  e.preventDefault();
  if(confirm('Vai tiešām vēlies iegādāties šo produktu?')){
    this.submit();
  }
});
</script>
@endauth
</x-app-layout>
