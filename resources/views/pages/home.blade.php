<x-app-layout title="VolleyPass – Sākumlapa">
    <div class="max-w-7xl mx-auto px-6 py-12">
       
        <div class="mb-10 rounded-xl bg-gradient-to-r from-orange-400 to-blue-600 text-white p-8 shadow-lg">
            <div class="grid md:grid-cols-2 gap-6 items-center">
                <div>
                    <h1 class="text-4xl font-bold">VolleyPass</h1>
                    <p class="mt-2">Īstā vieta, kur pirkt biļetes volejbola spēlēm.</p>
                </div>
                <div class="hidden md:flex justify-center">
                    <img src="{{ asset('images/volleyball.png') }}" alt="volleyball" class="h-28 w-auto">
                </div>
            </div>
        </div>

       
        <div class="grid lg:grid-cols-3 gap-8 items-start">
          
            <section class="lg:col-span-2 flex flex-col items-center">
                <h2 class="text-2xl font-semibold mb-6">Tuvākie pasākumi</h2>

                <div x-data="{
                        current: 0,
                        events: @json($events ?? []),
                        get total(){ return this.events.length }
                    }"
                     class="relative w-full">

                    
                    <div class="relative">
                        
                        <button @click="current = (current - 1 + total) % total"
                                class="absolute left-[-26px] top-1/2 -translate-y-1/2 hidden md:inline-flex items-center justify-center h-16 w-16 rounded-full bg-white shadow-lg">
                           
                            <svg class="w-8 h-8 text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        
                        <div class="h-80 bg-white rounded-2xl shadow-xl overflow-hidden">
                            <template x-if="total === 0">
                                <div class="h-full flex items-center justify-center text-gray-500">
                                    Pašlaik nav pieejamu pasākumu.
                                </div>
                            </template>

                            <template x-for="(evt, idx) in events" :key="idx">
                                <article x-show="current === idx" x-transition:enter="transition ease-out duration-300"
                                         x-transition:enter-start="opacity-0 transform translate-x-6"
                                         x-transition:enter-end="opacity-100 transform translate-x-0"
                                         x-transition:leave="transition ease-in duration-200"
                                         x-transition:leave-start="opacity-100"
                                         x-transition:leave-end="opacity-0"
                                         class="h-full p-6 flex gap-6">
                                    
                                    <div class="w-1/3 hidden md:block">
                                        <img :src="evt.image || '{{ asset('images/volleyball.png') }}'"
                                             class="h-full w-full object-cover rounded-lg" alt="" />
                                    </div>

                                    
                                    <div class="flex-1">
                                        <h3 class="text-2xl font-semibold text-gray-800" x-text="evt.title"></h3>
                                        <p class="mt-2 text-gray-600 line-clamp-3" x-text="evt.description || 'Apraksts nav pieejams.'"></p>

                                        <div class="mt-4 text-sm text-gray-500">
                                            <div><strong>Datums:</strong>
                                                <span x-text="(evt.event_date) ? (new Date(evt.event_date)).toLocaleString('lv-LV') : 'Nav datuma'"></span>
                                            </div>
                                            <div><strong>Vieta:</strong> <span x-text="evt.location || 'Nav vietas'"></span></div>
                                        </div>

                                        <div class="mt-6">
                                            <a href="#" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg shadow">Biļetes</a>
                                        </div>
                                    </div>
                                </article>
                            </template>
                        </div>

                        
                        <button @click="current = (current + 1) % total"
                                class="absolute right-[-26px] top-1/2 -translate-y-1/2 hidden md:inline-flex items-center justify-center h-16 w-16 rounded-full bg-white shadow-lg">
                            
                            <svg class="w-8 h-8 text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    
                    <div class="mt-4 flex items-center justify-center gap-4 md:hidden">
                        <button @click="current = (current - 1 + total) % total" class="px-3 py-2 bg-white rounded-md shadow">
                            Prev
                        </button>
                        <div class="flex items-center gap-2">
                            <template x-for="(evt, idx) in events" :key="idx">
                                <button @click="current = idx"
                                        :class="{'bg-blue-600': current === idx, 'bg-gray-300': current !== idx}"
                                        class="w-3 h-3 rounded-full"></button>
                            </template>
                        </div>
                        <button @click="current = (current + 1) % total" class="px-3 py-2 bg-white rounded-md shadow">
                            Next
                        </button>
                    </div>
                </div>
            </section>

            <!-- RIGHT (sidebar) -->
            <aside>
                <div class="bg-white rounded-2xl shadow-lg p-8 h-full flex flex-col items-center">
                    <h3 class="text-2xl font-bold mb-6">VolleyPass</h3>

                    <div class="w-full bg-orange-50 border border-orange-200 rounded-2xl p-6 mb-8 text-center">
                        <div class="text-gray-700 font-semibold">Populārākais pasākums</div>
                        <p class="text-sm text-gray-500 mt-2">Top notikums / biļešu ieteikums</p>
                    </div>

                    <a href="{{ route('about') }}" class="px-6 py-2 bg-blue-600 text-white rounded-full">Vairāk par mums</a>

                    <!-- optional small promo / placeholder -->
                    <div class="mt-6 text-sm text-gray-400 text-center">
                        <p>Sekojiet jaunumiem — drīzumā vairāk pasākumu.</p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
