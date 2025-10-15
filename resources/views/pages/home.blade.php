<x-app-layout title="VolleyPass – Sākumlapa">
    <div class="max-w-7xl mx-auto px-6 py-12">

        {{-- Hero --}}
        <div class="mb-10 rounded-xl bg-gradient-to-r from-orange-400 to-blue-600 text-white p-8 shadow-lg">
            <h1 class="text-4xl font-bold">VolleyPass</h1>
            <p class="mt-2">Īstā vieta, kur pirkt biļetes volejbola spēlēm.</p>
        </div>

        <div class="grid lg:grid-cols-3 gap-8 items-start">
            
            {{-- LEFT: Upcoming Matches --}}
            <section class="lg:col-span-2">
                <h2 class="text-2xl font-semibold mb-6">Tuvākie pasākumi</h2>

                <div 
                    x-data="{
                        matches: {{ json_encode($matches) }},
                        start: 0,
                        get visible() { 
                            return this.matches.slice(this.start, this.start + 3) 
                        },
                        next() {
                            if (this.start + 3 < this.matches.length) this.start++
                        },
                        prev() {
                            if (this.start > 0) this.start--
                        }
                    }"
                    class="relative"
                >
                    {{-- Matches --}}
                    <template x-for="match in visible" :key="match.id">
                        <div class="mb-6 p-6 bg-white rounded-2xl shadow-md">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-bold">
                                    <span x-text="match.home_team_name || 'TBA'"></span>
                                    <span class="text-gray-500">vs</span>
                                    <span x-text="match.away_team_name || 'TBA'"></span>
                                </h3>
                                <a :href="'/volleyball/' + match.id"
                                   class="text-blue-600 text-sm font-medium hover:underline">
                                    Skatīt
                                </a>
                            </div>

                            <div class="mt-2 text-sm text-gray-600">
                                <p><strong>Datums:</strong> 
                                    <span x-text="match.start_time ? new Date(match.start_time).toLocaleString('lv-LV') : 'Nav datuma'"></span>
                                </p>
                                
                            </div>
                        </div>
                    </template>

                    {{-- Arrows --}}
                    <button @click="prev" 
                        class="absolute left-[-30px] top-1/2 -translate-y-1/2 bg-white shadow p-3 rounded-full"
                        x-show="start > 0">
                        ←
                    </button>
                    <button @click="next" 
                        class="absolute right-[-30px] top-1/2 -translate-y-1/2 bg-white shadow p-3 rounded-full"
                        x-show="start + 3 < matches.length">
                        →
                    </button>
                </div>
            </section>

            {{-- RIGHT: Sidebar --}}
            <aside>
                <div class="bg-white rounded-2xl shadow-lg p-8 h-full flex flex-col items-center">
                    <h3 class="text-2xl font-bold mb-6">VolleyPass</h3>

                    {{-- Popular Match --}}
                    @if(!empty($popular))
                        <div class="w-full bg-orange-50 border border-orange-200 rounded-2xl p-6 mb-8 text-center">
                            <div class="text-gray-700 font-semibold mb-2">🌟 Populārākais pasākums</div>

                            <div class="mb-2 font-bold">
                                {{ $popular['home_team_name'] ?? 'TBA' }} 
                                <span class="text-gray-500">vs</span> 
                                {{ $popular['away_team_name'] ?? 'TBA' }}
                            </div>

                            <p class="text-sm text-gray-500 mt-1">
                                Datums: {{ !empty($popular['start_time']) ? \Carbon\Carbon::parse($popular['start_time'])->format('d.m.Y H:i') : 'Nav datuma' }}
                            </p>
                            <p class="text-sm text-gray-700 mt-2">Biļetes pārdotas: {{ $popularSold ?? 0 }}</p>

                            <a href="{{ route('volleyball.show', $popular['id']) }}"
                               class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg shadow">
                                Skatīt
                            </a>
                        </div>
                    @else
                        <div class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-6 mb-8 text-center text-gray-500">
                            Pašlaik nav populārā pasākuma.
                        </div>
                    @endif

                    <a href="{{ route('about') }}" class="px-6 py-2 bg-blue-600 text-white rounded-full">Vairāk par mums</a>

                    <div class="mt-6 text-sm text-gray-400 text-center">
                        <p>Sekojiet jaunumiem — drīzumā vairāk pasākumu.</p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
