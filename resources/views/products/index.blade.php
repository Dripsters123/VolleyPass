<x-app-layout>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Veikals</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $products->total() }} preces pieejamas</p>
        </div>
        @auth
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('products.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Pievienot preci
            </a>
            @else
            <a href="{{ route('product_requests.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Pieteikt produktu
            </a>
            @endif
        @endauth
    </div>

    @if(session('success'))
        <div class="mb-5 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Search & Filters --}}
    <form method="GET" action="{{ route('products.index') }}" class="mb-8 bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <div class="flex flex-col md:flex-row gap-3">
            {{-- Search --}}
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 0 5 11a6 6 0 0 0 12 0z"/>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Meklēt preces pēc nosaukuma…"
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            </div>

            {{-- Price min --}}
            <div class="relative w-full md:w-36">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 text-sm">€</span>
                <input type="number" name="min_price" value="{{ request('min_price') }}" min="0" step="0.01"
                    placeholder="Min cena"
                    class="w-full pl-7 pr-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            </div>

            {{-- Price max --}}
            <div class="relative w-full md:w-36">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 text-sm">€</span>
                <input type="number" name="max_price" value="{{ request('max_price') }}" min="0" step="0.01"
                    placeholder="Max cena"
                    class="w-full pl-7 pr-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            </div>

            {{-- Category --}}
            <select name="category"
                class="w-full md:w-52 px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition bg-white">
                <option value="">Visas kategorijas</option>
                @foreach($categories as $key => $label)
                    <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            {{-- Sort --}}
            <select name="sort"
                class="w-full md:w-52 px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition bg-white">
                <option value="" {{ !request('sort') ? 'selected' : '' }}>Kārtot: jaunākās</option>
                <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Cena: no zemākās</option>
                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Cena: no augstākās</option>
            </select>

            <button type="submit"
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition shadow-sm whitespace-nowrap">
                Filtrēt
            </button>

            @if(request()->hasAny(['search','min_price','max_price','sort','category']))
                <a href="{{ route('products.index') }}"
                   class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-medium rounded-xl transition whitespace-nowrap">
                    Notīrīt
                </a>
            @endif
        </div>
    </form>

    {{-- Product Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @forelse($products as $product)
            <div class="group bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden transition-all hover:-translate-y-1 hover:shadow-md">
                {{-- Image --}}
                <div class="relative h-48 bg-gray-50 overflow-hidden">
                    @if($product->image_path)
                        <img src="{{ asset('storage/'.$product->image_path) }}"
                             alt="{{ $product->title }}"
                             class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                    @else
                        <div class="h-full w-full flex items-center justify-center bg-gradient-to-br from-blue-50 to-blue-100">
                            <svg class="w-14 h-14 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                            </svg>
                        </div>
                    @endif
                    @if($product->status === 'sold' || $product->stock <= 0)
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                            <span class="px-3 py-1 bg-white text-gray-800 text-xs font-bold rounded-full tracking-wide">Izpārdots</span>
                        </div>
                    @elseif($product->stock <= 5)
                        <div class="absolute top-2 right-2">
                            <span class="px-2 py-0.5 bg-amber-500 text-white text-xs font-semibold rounded-full">Atlicis: {{ $product->stock }}</span>
                        </div>
                    @else
                        <div class="absolute top-2 right-2">
                            <span class="px-2 py-0.5 bg-green-600/90 text-white text-xs font-semibold rounded-full">{{ $product->stock }} gb.</span>
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="p-4">
                    <h3 class="font-semibold text-gray-900 text-sm leading-snug mb-1 line-clamp-2">{{ $product->title }}</h3>
                    <p class="text-xs text-gray-500 line-clamp-2 mb-3">{{ Str::limit($product->description, 80) }}</p>
                    <div class="flex items-center justify-between mt-auto">
                        <span class="text-lg font-bold text-blue-700">€{{ number_format($product->price, 2) }}</span>
                        <a href="{{ route('products.show', $product) }}"
                           class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition">
                            Apskatīt
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16 text-gray-400">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                </svg>
                <div class="font-medium text-gray-500">Nav nevienas preces</div>
                <div class="text-sm mt-1">Mēģiniet mainīt filtrus vai pievienojiet pirmo preci</div>
            </div>
        @endforelse
    </div>

    <div class="mt-8">{{ $products->links() }}</div>
</div>
</x-app-layout>

