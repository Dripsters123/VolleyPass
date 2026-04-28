<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Manas komandas</h2>
            <a href="{{ route('teams.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition shadow-sm">
                + Jauna komanda
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 py-8">

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm">{{ session('success') }}</div>
        @endif

        @if($teams->isEmpty())
            <div class="text-center py-20">
                <div class="text-5xl mb-4">🏐</div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Nav saglabātu komandu</h3>
                <p class="text-gray-500 text-sm mb-6">Izveidojiet komandu, lai ātri aizpildītu mača pieprasījumus.</p>
                <a href="{{ route('teams.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition">
                    Izveidot pirmo komandu
                </a>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach($teams as $team)
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-start gap-4">
                        @if($team->logo)
                            <img src="{{ Storage::url($team->logo) }}" class="w-14 h-14 rounded-xl object-cover flex-shrink-0 border" alt="">
                        @else
                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-xl font-bold">{{ strtoupper(substr($team->name, 0, 1)) }}</span>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-semibold text-gray-900 truncate">{{ $team->name }}</h3>
                                <span class="flex-shrink-0 text-xs text-gray-400 bg-gray-100 rounded-full px-2 py-0.5">
                                    {{ $team->players_per_team }}v{{ $team->players_per_team }}
                                </span>
                            </div>
                            @if($team->coach)
                                <p class="text-xs text-gray-400 mt-0.5">Treneris: {{ $team->coach }}</p>
                            @endif
                            <p class="text-xs text-gray-500 mt-1">{{ count($team->players ?? []) }} spēlētāji</p>
                            <ul class="mt-2 space-y-0.5">
                                @foreach(array_slice($team->players ?? [], 0, 3) as $p)
                                    <li class="text-xs text-gray-600">{{ $p['first_name'] ?? '' }} {{ $p['last_name'] ?? '' }}</li>
                                @endforeach
                                @if(count($team->players ?? []) > 3)
                                    <li class="text-xs text-gray-400">+ {{ count($team->players) - 3 }} vairāk…</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-auto px-5 pb-4 -mt-4 col-start-auto">
                        {{-- actions inline below card --}}
                    </div>
                @endforeach
            </div>

            {{-- Separate action bars --}}
            <div class="grid gap-4 sm:grid-cols-2 mt-0">
                @foreach($teams as $team)
                    <div class="flex gap-2 px-1">
                        <a href="{{ route('teams.edit', $team) }}"
                           class="flex-1 text-center px-3 py-2 text-sm font-medium border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-50 transition">
                            ✏️ Rediģēt
                        </a>
                        <form method="POST" action="{{ route('teams.destroy', $team) }}">
                            @csrf @method('DELETE')
                            <button type="button"
                                    onclick="vpConfirm('Dzēst komandu?', () => this.closest('form').submit(), { danger: true, confirmText: 'Dzēst' })"
                                    class="px-3 py-2 text-sm font-medium border border-red-200 rounded-xl text-red-600 hover:bg-red-50 transition">
                                🗑
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
