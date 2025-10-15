<x-app-layout>
<div class="container p-6">
  <h1 class="text-2xl mb-4">My Product Requests</h1>

  <a href="{{ route('product_requests.create') }}" class="btn btn-primary mb-4">New Product Request</a>

  @foreach($requests as $req)
    <div class="border rounded p-4 mb-3">
      <h2 class="font-semibold">{{ $req->title }}</h2>
      <p class="text-sm text-gray-600">{{ $req->description }}</p>
      <div class="flex justify-between mt-2">
        <span>Status: <strong>{{ ucfirst($req->status) }}</strong></span>
        @if($req->status === 'pending')
          <a href="{{ route('product_requests.edit', $req) }}" class="text-blue-600">Edit</a>
        @endif
      </div>
    </div>
  @endforeach

  {{ $requests->links() }}
</div>
</x-app-layout>
