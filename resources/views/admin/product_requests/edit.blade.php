<x-app-layout>
<div class="max-w-4xl mx-auto bg-white shadow-md rounded-lg p-6 mt-10">
    <h1 class="text-2xl font-bold mb-6 text-green-700">Apstiprināt un rediģēt produktu</h1>

    <form method="POST" action="{{ route('admin.product_requests.approve', $productRequest) }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-4">
            <label class="block font-semibold mb-1">Nosaukums</label>
            <input type="text" name="title" value="{{ old('title', $productRequest->title) }}"
                   class="w-full border rounded p-2">
            @error('title') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Apraksts</label>
            <textarea name="description" rows="4" class="w-full border rounded p-2">{{ old('description', $productRequest->description) }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Cena (€)</label>
            <input type="number" step="0.01" name="price" value="{{ old('price', $productRequest->price) }}"
                   class="w-full border rounded p-2">
            @error('price') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Attēls</label>
            @if($productRequest->image_path)
                <div class="mb-2">
                    <p class="text-sm text-gray-600 mb-1">Esošais attēls:</p>
                    <img src="{{ asset('storage/' . $productRequest->image_path) }}" 
                         alt="Current image"
                         class="rounded-lg max-h-40 border mb-2">
                </div>
            @endif

            <input type="file" name="image" accept="image/*" class="block w-full text-sm text-gray-700 border rounded p-2">
            <p class="text-gray-500 text-xs mt-1">Atļautie formāti: jpg, jpeg, png, svg (max 5MB)</p>
        </div>

        <div class="flex gap-3 mt-6">
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                Apstiprināt un izveidot produktu
            </button>

            <a href="{{ route('admin.product_requests.show', $productRequest) }}" 
               class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">
                Atpakaļ
            </a>
        </div>
    </form>
</div>
</x-app-layout>
