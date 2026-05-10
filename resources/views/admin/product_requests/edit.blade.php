<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Apstiprināt un rediģēt produktu</h2>
    </x-slot>

    @php
        $contactPhone = old('contact_phone', $productRequest->contact_phone);
        $phoneCode = old('phone_code', '+371');
        $phoneNumber = old('phone_number', '');

        if ($contactPhone) {
            preg_match('/^(\+\d{1,4})\s*(.*)$/', $contactPhone, $matches);
            if (!empty($matches[1])) {
                $phoneCode = $matches[1];
                $phoneNumber = trim($matches[2] ?? '');
            } else {
                $phoneNumber = $contactPhone;
            }
        }
    @endphp

    <div class="max-w-5xl mx-auto px-4 py-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">

            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Apstiprināt un rediģēt produktu</h1>

            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                Pārskatiet un rediģējiet produkta informāciju pirms apstiprināšanas. Pēc apstiprināšanas produkts tiks publicēts veikalā.
            </p>

            @if($errors->any())
                <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                    <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('admin.product_requests.approve', $productRequest) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="lg:grid lg:grid-cols-5 lg:gap-8">
                    <div class="lg:col-span-3 space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Produkta nosaukums *</label>
                            <input type="text" name="title" value="{{ old('title', $productRequest->title) }}" required
                                   class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Piemēram: Volejbola ceļgalu sargi">
                            @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Apraksts</label>
                            <textarea name="description" rows="4"
                                      class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Aprakstiet produktu un kāpēc tas būtu noderīgs…">{{ old('description', $productRequest->description) }}</textarea>
                            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ieteicamā cena (EUR) *</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">€</span>
                                    <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $productRequest->price) }}" required
                                           class="w-full pl-7 rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0.00">
                                </div>
                                @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategorija</label>
                                <select name="category"
                                        class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">-- Izvēlies kategoriju --</option>
                                    @foreach(config('products.categories') as $key => $label)
                                        <option value="{{ $key }}" {{ old('category', $productRequest->category) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('category') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Daudzums (skaits noliktavā) *</label>
                            <input type="number" name="stock" value="{{ old('stock', $productRequest->stock ?? 1) }}" required min="1" max="9999"
                                   class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Piemēram: 10">
                            @error('stock') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="border-t border-gray-100 dark:border-gray-700 pt-5">
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Kontaktinformācija</p>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilns vārds *</label>
                                    <input type="text" name="seller_full_name" value="{{ old('seller_full_name', $productRequest->seller_full_name) }}" required
                                           class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Jānis Bērziņš">
                                    @error('seller_full_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">E-pasts</label>
                                    <input type="email" name="contact_email" value="{{ old('contact_email', $productRequest->contact_email) }}"
                                           class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="tavs@epasts.lv">
                                    @error('contact_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tālrunis</label>
                                    <div class="flex gap-2">
                                        <select name="phone_code" class="rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-sm w-36">
                                            @php
                                            $phoneCodes = ['+371'=>'🇱🇻 +371','+370'=>'🇱🇹 +370','+372'=>'🇪🇪 +372','+358'=>'🇫🇮 +358','+46'=>'🇸🇪 +46','+47'=>'🇳🇴 +47','+45'=>'🇩🇰 +45','+49'=>'🇩🇪 +49','+44'=>'🇬🇧 +44','+33'=>'🇫🇷 +33','+31'=>'🇳🇱 +31','+48'=>'🇵🇱 +48','+34'=>'🇪🇸 +34','+39'=>'🇮🇹 +39','+7'=>'🇷🇺 +7','+380'=>'🇺🇦 +380','+1'=>'🇺🇸 +1'];
                                            @endphp
                                            @foreach($phoneCodes as $code => $label)
                                                <option value="{{ $code }}" {{ $phoneCode === $code ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <input type="tel" name="phone_number" value="{{ $phoneNumber }}"
                                               class="flex-1 rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="2X XXX XXX">
                                    </div>
                                    <input type="hidden" name="contact_phone" value="{{ old('contact_phone', $productRequest->contact_phone) }}">
                                    @error('contact_phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Adrese (kur atrodas prece)</label>
                                    <input type="text" name="address" value="{{ old('address', $productRequest->address) }}"
                                           class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Piemēram: Rīga, Brīvības iela 1">
                                    @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Aptuvens piegādes laiks (dienas)</label>
                                    <input type="number" name="delivery_days" value="{{ old('delivery_days', $productRequest->delivery_days) }}" min="1" max="365"
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
                            <img id="imagePreview" src="{{ $productRequest->image_path ? asset('storage/' . $productRequest->image_path) : '' }}" alt="" class="{{ $productRequest->image_path ? '' : 'hidden' }} absolute inset-0 w-full h-full object-cover rounded-xl">
                            <div id="imageDropLabel" class="{{ $productRequest->image_path ? 'hidden' : '' }} absolute inset-0 flex flex-col items-center justify-center pointer-events-none p-4">
                                <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-sm text-gray-500 text-center">Spied vai velc attēlu šeit</span>
                                <span class="text-xs text-gray-400 mt-1">JPG, PNG, WebP (bez GIF)</span>
                            </div>
                            <div id="imageChosen" class="{{ $productRequest->image_path ? '' : 'hidden' }} absolute bottom-0 inset-x-0 bg-black/50 text-white text-xs text-center py-1.5 truncate px-2">{{ $productRequest->image_path ? basename($productRequest->image_path) : '' }}</div>
                            <input id="imageInput" type="file" name="image" accept="image/jpeg,image/png,image/webp" class="hidden">
                        </label>
                        <p id="imageError" class="hidden mt-1 text-sm text-red-600">GIF attēli nav atļauti.</p>
                        @error('image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex gap-3 pt-4 mt-4 border-t border-gray-100 dark:border-gray-700">
                    <button type="submit"
                            class="flex-1 sm:flex-none sm:px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition shadow-sm">
                        Apstiprināt un izveidot produktu
                    </button>
                    <a href="{{ route('admin.product_requests.show', $productRequest) }}"
                       class="px-4 py-2.5 border border-gray-200 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition text-sm font-medium">
                        Atcelt
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/products/imageDropzone.js') }}"></script>
</x-app-layout>
