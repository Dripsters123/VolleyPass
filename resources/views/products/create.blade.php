<x-app-layout>
<div class="max-w-3xl mx-auto bg-white shadow-lg rounded-xl p-6 mt-6">
  <h1 class="text-2xl font-bold mb-4 text-gray-800">Izveidot produktu</h1>

  <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
    @csrf
    <div>
      <label class="block text-sm font-medium mb-1">Nosaukums</label>
      <input type="text" name="title" value="{{ old('title') }}" class="w-full border-gray-300 rounded-lg p-2 focus:ring focus:ring-blue-200">
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Apraksts</label>
      <textarea name="description" class="w-full border-gray-300 rounded-lg p-2 focus:ring focus:ring-blue-200">{{ old('description') }}</textarea>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Cena (EUR)</label>
      <input type="number" step="0.01" name="price" value="{{ old('price') }}" class="w-48 border-gray-300 rounded-lg p-2 focus:ring focus:ring-blue-200">
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Attēls</label>
      <input type="file" name="image" class="border-gray-300">
    </div>

    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Izveidot</button>
  </form>
</div>
</x-app-layout>
