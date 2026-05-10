<x-app-layout>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mani produkti</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $products->total() }} produkti kopā</p>
        </div>
        @if(auth()->user()->role === 'admin')
            <a href="{{ route('products.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Pievienot produktu
            </a>
        @else
            <a href="{{ route('product_requests.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Pieteikt jaunu produktu
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-5 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($products->isEmpty())
        <div class="text-center py-20 text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
            </svg>
            <p class="font-medium text-gray-500">Jums vēl nav produktu veikalā</p>
            <p class="text-sm mt-1">Pieteiciet jaunu produktu un tas nonāks veikalā pēc apstiprināšanas.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($products as $product)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                   
                    <div class="relative h-44 bg-gray-50 overflow-hidden shrink-0">
                        @if($product->image_path)
                            <img src="{{ asset('storage/'.$product->image_path) }}"
                                 alt="{{ $product->title }}"
                                 class="h-full w-full object-cover">
                        @else
                            <div class="h-full w-full flex items-center justify-center bg-gradient-to-br from-blue-50 to-blue-100">
                                <svg class="w-12 h-12 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                </svg>
                            </div>
                        @endif

                     
                        <div class="absolute top-2 left-2">
                            @if($product->status === 'sold' || $product->stock <= 0)
                                <span class="px-2 py-0.5 bg-red-600 text-white text-xs font-semibold rounded-full">Izpārdots</span>
                            @elseif($product->status === 'active')
                                <span class="px-2 py-0.5 bg-green-600 text-white text-xs font-semibold rounded-full">Aktīvs</span>
                            @else
                                <span class="px-2 py-0.5 bg-gray-500 text-white text-xs font-semibold rounded-full">{{ ucfirst($product->status) }}</span>
                            @endif
                        </div>

                        
                        <div class="absolute top-2 right-2">
                            @if($product->stock <= 0)
                                <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded-full">0 gb.</span>
                            @elseif($product->stock <= 5)
                                <span class="px-2 py-0.5 bg-amber-500 text-white text-xs font-semibold rounded-full">Atlicis: {{ $product->stock }}</span>
                            @else
                                <span class="px-2 py-0.5 bg-white/90 text-gray-700 text-xs font-semibold rounded-full shadow-sm">{{ $product->stock }} gb.</span>
                            @endif
                        </div>
                    </div>

                  
                    <div class="p-4 flex flex-col flex-1">
                        <h3 class="font-semibold text-gray-900 text-sm leading-snug mb-1 line-clamp-2">{{ $product->title }}</h3>
                        @if($product->category)
                            @php $catLabel = config('products.categories')[$product->category] ?? ucfirst($product->category); @endphp
                            <span class="inline-block text-xs text-blue-600 mb-2">{{ $catLabel }}</span>
                        @endif
                        <span class="text-lg font-bold text-blue-700 mb-3">€{{ number_format($product->price, 2) }}</span>

                    
                        <div class="mt-auto border-t border-gray-100 pt-3">
                            <p class="text-xs text-gray-500 mb-2 font-medium">Papildināt noliktavu</p>
                            <form method="POST" action="{{ route('products.restock', $product) }}" class="flex gap-2">
                                @csrf
                                <input type="number" name="quantity" min="1" max="9999" value="1"
                                       class="w-20 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 px-2">
                                <button type="submit"
                                        class="flex-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition">
                                    + Pievienot
                                </button>
                            </form>
                        </div>

                        <a href="{{ route('products.show', $product) }}"
                           class="mt-2 block text-center text-xs text-gray-500 hover:text-gray-700 transition">
                            Apskatīt veikalā →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">{{ $products->links() }}</div>
    @endif
</div>
</x-app-layout>
