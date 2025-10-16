<x-app-layout>
  <div class="max-w-4xl mx-auto bg-white shadow-md rounded-lg p-6 mt-10">
    <h1 class="text-2xl font-bold mb-6 text-green-700">Produkta pieprasījums #{{ $productRequest->id }}</h1>

    @if(session('success'))
      <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="text-xs text-gray-600">Nosaukums</label>
        <div class="font-medium">{{ $productRequest->title ?? '—' }}</div>
      </div>

      <div>
        <label class="text-xs text-gray-600">Cena (EUR)</label>
        <div class="font-medium">€{{ number_format($productRequest->price ?? 0, 2) }}</div>
      </div>

      <div class="md:col-span-2">
        <label class="text-xs text-gray-600">Apraksts</label>
        <div class="text-gray-700">{{ $productRequest->description ?? '—' }}</div>
      </div>

      @if($productRequest->image_path)
        <div class="md:col-span-2">
          <label class="text-xs text-gray-600">Attēls</label>
          <div class="mt-2">
            <img src="{{ asset('storage/' . $productRequest->image_path) }}" class="max-h-48 rounded border">
          </div>
        </div>
      @endif

      <div>
        <label class="text-xs text-gray-600">Pieprasīja</label>
        <div class="font-medium">{{ optional($productRequest->user)->name ?? '—' }}</div>
        @if(optional($productRequest->user)->email)
          <div class="text-xs text-gray-500">{{ optional($productRequest->user)->email }}</div>
        @endif
      </div>

      <div>
        <label class="text-xs text-gray-600">Statuss</label>
        <div>
          <span class="
            @if($productRequest->status === 'pending') text-yellow-600
            @elseif($productRequest->status === 'approved') text-green-600
            @else text-red-600 @endif
          ">
            {{ ucfirst($productRequest->status ?? 'pending') }}
          </span>
        </div>
      </div>
    </div>

    <div class="flex gap-3 mt-6">
      <form method="GET" action="{{ route('admin.product_requests.edit', $productRequest) }}">
        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Apstiprināt / Rediģēt</button>
      </form>

      <form method="POST" action="{{ route('admin.product_requests.reject', $productRequest) }}">
        @csrf
        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Noraidīt</button>
      </form>

      <a href="{{ route('admin.match_requests.inbox') }}" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Atpakaļ uz iesūtni</a>
    </div>
  </div>
</x-app-layout>
