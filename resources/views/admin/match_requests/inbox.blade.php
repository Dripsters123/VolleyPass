<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Admin paskastīte
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <form method="GET" class="mb-4 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="text-sm text-gray-600 block">Type</label>
                    <select name="type" class="mt-1 rounded border-gray-300">
                        <option value="">All</option>
                        <option value="match" {{ request('type') === 'match' ? 'selected' : '' }}>Maču pieprasījumi</option>
                        <option value="score_update" {{ request('type') === 'score_update' ? 'selected' : '' }}>Rezultātu pieprasījumi</option>
                        <option value="product" {{ request('type') === 'product' ? 'selected' : '' }}>Produktu pieprasījumi</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm text-gray-600 block">Lietotājs</label>
                    <input name="user" value="{{ request('user') }}" class="mt-1 rounded border-gray-300 px-2" placeholder="Lietotāja vārds">
                </div>

                <div>
                    <label class="text-sm text-gray-600 block">Sākuma datums</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="mt-1 rounded border-gray-300 px-2">
                </div>

                <div>
                    <label class="text-sm text-gray-600 block">Beigu datums</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="mt-1 rounded border-gray-300 px-2">
                </div>

                <div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Filtrēt</button>
                </div>

                <div>
                    <a href="{{ route('admin.match_requests.inbox') }}" class="text-sm text-gray-600 underline">Notīrīt</a>
                </div>
            </form>

            <div class="hidden md:block bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border text-left">Pieprasījuma tips</th>
                            <th class="px-4 py-2 border text-left">Sūtītājs</th>
                            <th class="px-4 py-2 border text-left">Uzskaite</th>
                            <th class="px-4 py-2 border text-center">Izveidots</th>
                            <th class="px-4 py-2 border text-center">Statuss</th>
                            <th class="px-4 py-2 border text-center">Darbības</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($requests as $item)
                            @php
                                $rawDate = $item->created_at ?? ($item->start_time ?? null);
                                $displayDate = null;

                                if ($rawDate) {
                                    // If it's already a DateTime (Carbon is DateTimeInterface), format directly
                                    if ($rawDate instanceof \DateTimeInterface) {
                                        // ensure timezone conversion to Europe/Riga
                                        $displayDate = \Carbon\Carbon::instance($rawDate)->timezone('Europe/Riga');
                                    } else {
                                        // otherwise attempt to parse — guard with try/catch
                                        try {
                                            $displayDate = \Carbon\Carbon::parse($rawDate)->timezone('Europe/Riga');
                                        } catch (\Throwable $e) {
                                            $displayDate = null;
                                        }
                                    }
                                }
                            @endphp

                            <tr class="text-gray-700 hover:bg-gray-50">

                                <td class="px-4 py-2 border capitalize">
                                    {{ $item->inbox_type ?? 'request' }}
                                </td>

                                <td class="px-4 py-2 border">
                                    {{ optional($item->user)->name ?? '—' }}
                                    @if(optional($item->user)->email)
                                        <div class="text-xs text-gray-500">{{ optional($item->user)->email }}</div>
                                    @endif
                                </td>

                                <td class="px-4 py-2 border">
                                    @if(($item->inbox_type ?? null) === 'product')
                                        @if(isset($item->product_name))
                                            <div class="font-medium">{{ $item->product_name }}</div>
                                        @else
                                            <div class="font-medium">Produkta pieprasījums #{{ $item->id }}</div>
                                        @endif
                                        <div class="text-sm text-gray-600 mt-1">
                                            {{ \Illuminate\Support\Str::limit($item->notes ?? $item->description ?? '', 120) }}
                                        </div>
                                    @else
                                        <div class="font-medium">
                                            {{ $item->home_team ?? ($item->home_team_name ?? 'Home') }}
                                            <span class="text-gray-400 mx-1">vs</span>
                                            {{ $item->away_team ?? ($item->away_team_name ?? 'Away') }}
                                        </div>
                                        <div class="text-sm text-gray-600 mt-1">
                                            Start:
                                            @if(isset($item->start_time))
                                                {{ \Carbon\Carbon::parse($item->start_time)->timezone('Europe/Riga')->format('d.m.Y H:i') }}
                                            @else
                                                —
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                <td class="px-4 py-2 border text-center text-sm text-gray-600">
                                    @if($displayDate)
                                        {{ $displayDate->format('d.m.Y H:i') }}
                                    @else
                                        —
                                    @endif
                                </td>

                                <td class="px-4 py-2 border text-center">
                                    <span class="inline-block px-2 py-1 rounded-full text-sm font-medium
                                        {{ ($item->status ?? '') === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ ($item->status ?? '') === 'accepted' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ ($item->status ?? '') === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                    ">
                                        {{ ucfirst($item->status ?? 'pending') }}
                                    </span>
                                </td>

                             
                               <td class="px-4 py-2 border text-center space-x-2">
    @if(($item->inbox_type ?? '') === 'product')
        <a href="{{ route('admin.product_requests.show', $item->id) }}" class="text-sm text-blue-600 hover:underline">Skatīt</a>
    @else
        <a href="{{ route('admin.match_requests.show', $item->id) }}" class="text-sm text-blue-600 hover:underline">Skatīt</a>
    @endif
</td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-4 border text-center text-gray-500">No requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4 px-4">
                    {{ $requests->links() }}
                </div>
            </div>

            <div class="md:hidden space-y-4">
                @forelse($requests as $item)
                    @php
                        $rawDate = $item->created_at ?? ($item->start_time ?? null);
                        $displayDate = null;
                        if ($rawDate) {
                            if ($rawDate instanceof \DateTimeInterface) {
                                $displayDate = \Carbon\Carbon::instance($rawDate)->timezone('Europe/Riga');
                            } else {
                                try {
                                    $displayDate = \Carbon\Carbon::parse($rawDate)->timezone('Europe/Riga');
                                } catch (\Throwable $e) {
                                    $displayDate = null;
                                }
                            }
                        }
                    @endphp

                    <div class="bg-white shadow rounded-lg p-4 border hover:bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-semibold">{{ ucfirst($item->inbox_type ?? 'request') }}</div>
                                <div class="text-sm text-gray-600">{{ optional($item->user)->name ?? '—' }}</div>
                            </div>
                            <div class="text-sm text-gray-500">
                                @if($displayDate)
                                    {{ $displayDate->format('d.m.Y H:i') }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>

                        <div class="mt-2">
                            @if(($item->inbox_type ?? '') === 'product')
                                <div class="font-medium">{{ $item->product_name ?? "Produkts #{$item->id}" }}</div>
                                <div class="text-sm text-gray-600 mt-1">{{ \Illuminate\Support\Str::limit($item->notes ?? $item->description ?? '', 150) }}</div>
                            @else
                                <div class="font-medium">{{ $item->home_team ?? $item->home_team_name ?? 'Home' }} vs {{ $item->away_team ?? $item->away_team_name ?? 'Away' }}</div>
                                <div class="text-sm text-gray-600 mt-1">
                                    Spēles sākums:
                                    @if(isset($item->start_time))
                                        {{ \Carbon\Carbon::parse($item->start_time)->timezone('Europe/Riga')->format('d.m.Y H:i') }}
                                    @else
                                        —
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="mt-3 flex gap-2">
                            @if(($item->inbox_type ?? '') === 'product')
  <a href="{{ route('admin.product_requests.show', $item->id) }}" class="text-sm text-blue-600 hover:underline">Skatīt</a>
@else
  <a href="{{ route('admin.match_requests.show', $item->id) }}" class="text-sm text-blue-600 hover:underline">Skatīt</a>
@endif
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-500">No requests found.</div>
                @endforelse

                <div class="mt-4 px-2">
                    {{ $requests->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
