<x-app-layout title="Rediģēt preci – VolleyPass">
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Rediģēt preci</h2>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 py-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Rediģēt preci</h1>

            @if($errors->any())
                <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                    <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="lg:grid lg:grid-cols-5 lg:gap-8">
                    
                    <div class="lg:col-span-3 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nosaukums *</label>
                            <input type="text" name="title" value="{{ old('title', $product->title) }}" required
                                   class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Piemēram: Volejbola bumba Mikasa V300W">
                            @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Apraksts</label>
                            <textarea name="description" rows="4"
                                      class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Produkta apraksts…">{{ old('description', $product->description) }}</textarea>
                            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cena (EUR) *</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">€</span>
                                    <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" required
                                           class="w-full pl-7 rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0.00">
                                </div>
                                @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div x-data="{ cat: '{{ old('category', $product->category ?? '') }}' }">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategorija</label>
                                <select name="category" x-model="cat"
                                        class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">-- Izvēlies kategoriju --</option>
                                    @foreach(config('products.categories') as $key => $label)
                                        <option value="{{ $key }}" {{ old('category', $product->category) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                    <option value="_other" {{ (old('category', $product->category) && !in_array(old('category', $product->category), array_keys(config('products.categories', [])))) ? 'selected' : '' }}>Cita (norādīt)</option>
                                </select>
                                <input x-show="cat === '_other'" x-cloak
                                       type="text" name="category_custom" value="{{ old('category_custom', (old('category', $product->category) && !in_array(old('category', $product->category), array_keys(config('products.categories', [])))) ? $product->category : '') }}"
                                       class="mt-2 w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Ievadi kategoriju">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Daudzums noliktavā *</label>
                            <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" required min="0" max="9999"
                                   class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="1">
                            @error('stock') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        @if(auth()->user()->role === 'admin')
                        <div class="border-t border-gray-100 dark:border-gray-700 pt-5">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Statuss (tikai administratoram)</label>
                            <select name="status" class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                                <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Aktīvs</option>
                                <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Neaktīvs</option>
                                <option value="sold" {{ old('status', $product->status) === 'sold' ? 'selected' : '' }}>Izpārdots</option>
                            </select>
                        </div>
                        @endif

                        <div class="border-t border-gray-100 dark:border-gray-700 pt-5">
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Pārdevēja kontaktinformācija</p>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilns vārds</label>
                                    <input type="text" name="seller_full_name" value="{{ old('seller_full_name', $product->seller_full_name) }}"
                                           class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Jānis Bērziņš">
                                    @error('seller_full_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">E-pasts</label>
                                    <input type="email" name="contact_email" value="{{ old('contact_email', $product->contact_email) }}"
                                           class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="pārdevējs@epasts.lv">
                                    @error('contact_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tālrunis</label>
                                    <div class="flex gap-2">
                                        <select name="phone_code" class="rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-sm w-36">
                                            @php
                                            $phoneCodes = ['+371'=>'🇱🇻 +371','+370'=>'🇱🇹 +370','+372'=>'🇪🇪 +372','+358'=>'🇫🇮 +358','+46'=>'🇸🇪 +46','+47'=>'🇳🇴 +47','+45'=>'🇩🇰 +45','+49'=>'🇩🇪 +49','+44'=>'🇬🇧 +44','+33'=>'🇫🇷 +33','+31'=>'🇳🇱 +31','+48'=>'🇵🇱 +48','+34'=>'🇪🇸 +34','+39'=>'🇮🇹 +39','+7'=>'🇷🇺 +7','+380'=>'🇺🇦 +380','+1'=>'🇺🇸 +1'];
                                            $existingPhone = old('phone_number', $product->contact_phone);
                                            $code = '+371';
                                            if ($existingPhone) {
                                                foreach ($phoneCodes as $c => $l) {
                                                    if (strpos($existingPhone, $c) === 0) {
                                                        $code = $c;
                                                        break;
                                                    }
                                                }
                                            }
                                            $oldCode = old('phone_code', $code);
                                            @endphp
                                            @foreach($phoneCodes as $c => $label)
                                                <option value="{{ $c }}" {{ $oldCode === $c ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <input type="tel" name="phone_number" value="{{ old('phone_number', $existingPhone ? substr($existingPhone, strlen($code)) : '') }}"
                                               class="flex-1 rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="2X XXX XXX">
                                    </div>
                                    @error('contact_phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Adrese (kur atrodas prece)</label>
                                    <input type="text" name="address" value="{{ old('address', $product->address) }}"
                                           class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Piemēram: Rīga, Brīvības iela 1">
                                    @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Piegādes laiks (dienas)</label>
                                    <input type="number" name="delivery_days" value="{{ old('delivery_days', $product->delivery_days) }}" min="1" max="365"
                                           class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Piemēram: 3">
                                    @error('delivery_days') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2 mt-5 lg:mt-0 flex flex-col">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Attēls</label>
                        <label id="imageDropZone"
                               class="relative w-full aspect-square border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition overflow-hidden block">
                            <img id="imagePreview" src="{{ $product->image_path ? asset('storage/' . $product->image_path) : '' }}" alt="" class="{{ $product->image_path ? '' : 'hidden' }} absolute inset-0 w-full h-full object-cover rounded-xl">
                            <div id="imageDropLabel" class="{{ $product->image_path ? 'hidden' : '' }} absolute inset-0 flex flex-col items-center justify-center pointer-events-none p-4">
                                <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-sm text-gray-500 text-center">Spied vai velc attēlu šeit</span>
                                <span class="text-xs text-gray-400 mt-1">JPG, PNG, WebP (bez GIF)</span>
                            </div>
                            <div id="imageChosen" class="{{ $product->image_path ? '' : 'hidden' }} absolute bottom-0 inset-x-0 bg-black/50 text-white text-xs text-center py-1.5 truncate px-2"></div>
                            <input id="imageInput" type="file" name="image" accept="image/jpeg,image/png,image/webp" class="hidden">
                        </label>
                        <p id="imageError" class="hidden mt-1 text-sm text-red-600">GIF attēli nav atļauti.</p>
                        @error('image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex gap-3 pt-4 mt-4 border-t border-gray-100 dark:border-gray-700">
                    <button type="submit"
                            class="flex-1 sm:flex-none sm:px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition shadow-sm">
                        Saglabāt izmaiņas
                    </button>
                    <a href="{{ route('products.show', $product) }}"
                       class="px-4 py-2.5 border border-gray-200 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition text-sm font-medium">
                        Atcelt
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/products/imageDropzone.js') }}"></script>
</x-app-layout>
