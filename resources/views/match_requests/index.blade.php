<x-app-layout>
<div class="max-w-5xl mx-auto p-6 bg-white rounded mt-8 shadow">
    <h1 class="text-2xl font-bold mb-4 text-blue-700">Mani pieprasījumi</h1>

    @if($requests->isEmpty())
        <p class="text-gray-600">Jūs vēl neesat nosūtījis nevienu pieprasījumu.</p>
    @else
        <div class="space-y-4">
            @foreach($requests as $r)
                <div class="p-4 border rounded-md bg-gray-50 hover:bg-gray-100 transition">
                    <div class="flex items-center justify-between flex-wrap">
                        <div>
                            @if($r->type === 'match' || $r->type === 'score_update')
                                <div class="font-semibold text-lg">{{ $r->home_team }} vs {{ $r->away_team }}</div>
                                <div class="text-sm text-gray-600">
                                    {{ \Carbon\Carbon::parse($r->start_time)->format('Y-m-d H:i') }} — {{ \Carbon\Carbon::parse($r->end_time)->format('Y-m-d H:i') }}
                                </div>
                                <div class="text-sm mt-1">Tips: {{ $r->request_type }} — Statuss: {{ ucfirst($r->status) }}</div>
                            @elseif($r->type === 'product')
                                <div class="font-semibold text-lg">{{ $r->title }}</div>
                                <div class="text-sm text-gray-600">{{ $r->description }}</div>
                                <div class="text-sm mt-1">Statuss: {{ ucfirst($r->status) }}</div>
                            @endif
                        </div>

                        <div class="text-right mt-2 sm:mt-0 flex gap-2">
                            @if($r->type === 'match' || $r->type === 'score_update')
                                <a href="{{ route('match_requests.view', $r->id) }}" class="px-3 py-1 bg-blue-600 text-white rounded text-sm">Skatīt</a>
                                @if($r->status === 'pending')
                                    <a href="{{ route('match_requests.edit', $r->id) }}" class="px-3 py-1 bg-yellow-500 text-white rounded text-sm">Rediģēt</a>
                                @endif
                            @elseif($r->type === 'product')
                                @if($r->status === 'pending')
                                    <a href="{{ route('product_requests.edit', $r->id) }}" class="px-3 py-1 bg-yellow-500 text-white rounded text-sm">Rediģēt</a>

                                    <!-- Cancel Button -->
                                    <form action="{{ route('match_requests.my', $r->id) }}" method="POST" onsubmit="return confirm('Vai tiešām vēlaties atcelt šo pieprasījumu?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded text-sm">Atcelt</button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{ $requests->links() }}
</div>
</x-app-layout>
