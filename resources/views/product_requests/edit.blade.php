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
            <label class="block mb-1">Attēls</label>
            <label id="imageDropZone"
                   class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition relative overflow-hidden
                          {{ $productRequest->image_path ? 'border-blue-400' : 'border-gray-300' }}">
                <img id="imagePreview"
                     src="{{ $productRequest->image_path ? asset('storage/' . $productRequest->image_path) : '' }}"
                     alt=""
                     class="{{ $productRequest->image_path ? '' : 'hidden' }} absolute inset-0 w-full h-full object-cover rounded-xl">
                <div id="imageDropLabel" class="{{ $productRequest->image_path ? 'hidden' : '' }} flex flex-col items-center pointer-events-none">
                    <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-sm text-gray-500">Spied vai velc attēlu šeit</span>
                </div>
                <div id="imageChosen" class="{{ $productRequest->image_path ? '' : 'hidden' }} absolute bottom-0 inset-x-0 bg-black/50 text-white text-xs text-center py-1 truncate px-2">
                    {{ $productRequest->image_path ? basename($productRequest->image_path) : '' }}
                </div>
                <input id="imageInput" type="file" name="image" class="hidden" accept="image/*">
            </label>
        </div>

        <button class="px-4 py-2 bg-green-600 text-white rounded">Rediģēt</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('imageInput');
    const preview = document.getElementById('imagePreview');
    const label = document.getElementById('imageDropLabel');
    const chosen = document.getElementById('imageChosen');
    const zone = document.getElementById('imageDropZone');

    function handleFile(file) {
        if (!file || !file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            label.classList.add('hidden');
            chosen.textContent = file.name;
            chosen.classList.remove('hidden');
            zone.classList.add('border-blue-400');
            zone.classList.remove('border-gray-300');
        };
        reader.readAsDataURL(file);
    }

    input.addEventListener('change', function () { handleFile(this.files[0]); });

    zone.addEventListener('dragover', function (e) { e.preventDefault(); zone.classList.add('border-blue-400'); });
    zone.addEventListener('dragleave', function () { if (!preview.src) zone.classList.remove('border-blue-400'); });
    zone.addEventListener('drop', function (e) {
        e.preventDefault();
        const dt = new DataTransfer();
        dt.items.add(e.dataTransfer.files[0]);
        input.files = dt.files;
        handleFile(e.dataTransfer.files[0]);
    });
});
</script>
</x-app-layout>
