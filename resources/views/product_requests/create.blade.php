<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Pieteikt produkta pieprasījumu</h2>
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 py-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">

            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                Iesniedz pieprasījumu pievienot produktu veikalā. Administrators to izskatīs un apstiprinās.
            </p>

            @if($errors->any())
                <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                    <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('product_requests.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Produkta nosaukums *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Piemēram: Volejbola ceļgalu sargi">
                    @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Apraksts</label>
                    <textarea name="description" rows="4"
                              class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Aprakstiet produktu un kāpēc tas būtu noderīgs…">{{ old('description') }}</textarea>
                    @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ieteicamā cena (EUR) *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">€</span>
                            <input type="number" step="0.01" min="0" name="price" value="{{ old('price') }}" required
                                   class="w-full pl-7 rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="0.00">
                        </div>
                        @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategorija</label>
                        <input type="text" name="category" value="{{ old('category') }}"
                               class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Piemēram: Aizsardzība">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Attēls</label>
                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                        <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-sm text-gray-500">Spied vai velc attēlu šeit</span>
                        <input type="file" name="image" accept="image/*" class="hidden">
                    </label>
                    @error('image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition shadow-sm">
                        Iesniegt pieprasījumu
                    </button>
                    <a href="{{ route('products.index') }}"
                       class="px-4 py-2.5 border border-gray-200 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition text-sm font-medium">
                        Atcelt
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>