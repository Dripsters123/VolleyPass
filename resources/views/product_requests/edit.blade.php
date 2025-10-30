<x-app-layout>
<div class="max-w-2xl mx-auto mt-10 bg-white p-6 rounded shadow">
    <h1 class="text-xl font-bold mb-4">Rediģēt produkta pieprasījumu</h1>

    <form method="POST" action="{{ route('product_requests.update', $productRequest) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="block">Nosaukums</label>
            <input type="text" name="title" class="border p-2 w-full" value="{{ old('title', $productRequest->title) }}" required>
        </div>

        <div class="mb-3">
            <label class="block">Apraksts</label>
            <textarea name="description" class="border p-2 w-full">{{ old('description', $productRequest->description) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="block">Cena</label>
            <input type="number" step="0.01" name="price" class="border p-2 w-full" value="{{ old('price', $productRequest->price) }}" required>
        </div>

        <div class="mb-3">
            <label class="block">Attēls</label>
            @if($productRequest->image_path)
                <img src="{{ asset('storage/' . $productRequest->image_path) }}" class="h-24 mb-2 rounded">
            @endif
            <input type="file" name="image">
        </div>

        <button class="px-4 py-2 bg-green-600 text-white rounded">Rediģēt</button>
    </form>
</div>
</x-app-layout>
