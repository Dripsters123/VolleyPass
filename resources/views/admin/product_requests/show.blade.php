<x-app-layout>
<div class="max-w-4xl mx-auto bg-white shadow-md rounded-lg p-6 mt-10">
    <h1 class="text-2xl font-bold mb-6 text-green-700">Produkta pieprasījums</h1>

    {{-- ✅ Flash messages --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
            {{ session('error') }}
        </div>
    @endif

    {{-- ✅ Product request details --}}
    <div class="mb-4">
        <div class="font-semibold text-lg mb-1">{{ $productRequest->title }}</div>
        <div class="text-gray-600 mb-2">{{ $productRequest->description }}</div>
        <div class="text-sm mb-1">Cena: €{{ number_format($productRequest->price, 2) }}</div>

        @if($productRequest->image_path)
            <div class="my-3">
                <img src="{{ asset('storage/' . $productRequest->image_path) }}" class="max-h-48 rounded border">
            </div>
        @endif

        <div class="text-xs text-gray-500">
            Pieprasīja: {{ optional($productRequest->user)->name ?? '—' }}
        </div>
        <div class="text-xs text-gray-500">
            Statuss: 
            <span class="
                @if($productRequest->status === 'pending') text-yellow-600 
                @elseif($productRequest->status === 'approved') text-green-600 
                @else text-red-600 
                @endif
            ">
                {{ ucfirst($productRequest->status) }}
            </span>
        </div>
    </div>

    {{-- ✅ Action buttons --}}
    <div class="flex gap-3 mt-6">
        <form method="GET" action="{{ route('admin.product_requests.edit', $productRequest) }}">
            <button type="submit" 
                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                Apstiprināt / Rediģēt
            </button>
        </form>

        <form method="POST" action="{{ route('admin.product_requests.reject', $productRequest) }}">
            @csrf
            <button type="submit" 
                class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                Noraidīt
            </button>
        </form>

        <a href="{{ url()->previous() }}" 
           class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">
           Atpakaļ
        </a>
    </div>
</div>
</x-app-layout>
