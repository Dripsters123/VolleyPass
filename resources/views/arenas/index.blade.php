<x-app-layout title="Arēnas – VolleyPass">

    {{-- Page header --}}
    <section class="bg-gray-950 border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-orange-400 mb-1">Arēnas</p>
                <h1 class="text-2xl font-extrabold text-white tracking-tight">Manas arēnas</h1>
            </div>
            <a href="{{ route('arenas.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-r from-orange-500 to-blue-600 text-white hover:opacity-90 transition-opacity">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v16m8-8H4"/>
                </svg>
                Jauna arēna
            </a>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ tab: 'mine' }">

        @if(session('success'))
            <div class="mb-6 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Tabs --}}
        <div class="flex gap-1 bg-gray-100 p-1 rounded-xl mb-8 w-fit">
            <button @click="tab = 'mine'"
                    :class="tab === 'mine' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="px-5 py-2 rounded-lg text-sm font-medium transition-all">
                Manas
                <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-600">{{ $myArenas->count() }}</span>
            </button>
            <button @click="tab = 'favorites'"
                    :class="tab === 'favorites' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="px-5 py-2 rounded-lg text-sm font-medium transition-all">
                Izlase
                <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-600">{{ $favorites->count() }}</span>
            </button>
            <button @click="tab = 'default'"
                    :class="tab === 'default' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="px-5 py-2 rounded-lg text-sm font-medium transition-all">
                Noklusējuma izkārtojumi
                <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-gray-200 text-gray-600">{{ $defaultLayouts->count() }}</span>
            </button>
        </div>

        {{-- My Arenas --}}
        <div x-show="tab === 'mine'">
            @if($myArenas->isEmpty())
                @include('arenas._empty', ['message' => 'Nav izveidotu arēnu. Sāc ar jaunu!'])
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                    @foreach($myArenas as $arena)
                        @include('arenas._card', ['arena' => $arena, 'canEdit' => true])
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Favorites --}}
        <div x-show="tab === 'favorites'">
            @if($favorites->isEmpty())
                @include('arenas._empty', ['message' => 'Nav izlases arēnu. Pievienojies publiskām arēnām!'])
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                    @foreach($favorites as $arena)
                        @include('arenas._card', ['arena' => $arena, 'canEdit' => false])
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Default Layouts --}}
        <div x-show="tab === 'default'">
            @if($defaultLayouts->isEmpty())
                @include('arenas._empty', ['message' => 'Nav noklusējuma izkārtojumu.'])
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                    @foreach($defaultLayouts as $arena)
                        @include('arenas._card', ['arena' => $arena, 'canEdit' => false])
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
