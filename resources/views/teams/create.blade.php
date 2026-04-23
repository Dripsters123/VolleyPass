<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Izveidot komandu</h2>
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 py-8">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

            @if($errors->any())
                <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                    <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('teams.store') }}" enctype="multipart/form-data"
                  x-data="teamForm({{ old('players_per_team', 6) }}, {{ json_encode(old('players', [])) }})">
                @csrf

                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Komandas nosaukums *</label>
                        <input name="name" value="{{ old('name') }}" required
                               class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Piemēram: Rīgas Vilki">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Treneris</label>
                        <input name="coach" value="{{ old('coach') }}"
                               class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Treneris (nav obligāts)">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logo (attēls)</label>
                        <input type="file" name="logo" accept="image/*"
                               class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-blue-50 file:text-blue-700 file:font-medium hover:file:bg-blue-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Formāts *</label>
                        <select name="players_per_team" x-model.number="perTeam" @change="resizePlayers()"
                                class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                            <option value="2">2 pret 2</option>
                            <option value="4">4 pret 4</option>
                            <option value="6" selected>6 pret 6</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Spēlētāji *</label>
                        <div class="space-y-2">
                            <template x-for="(p, i) in players" :key="i">
                                <div class="flex items-center gap-2">
                                    <span class="flex-shrink-0 w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center" x-text="i+1"></span>
                                    <input :name="`players[${i}][first_name]`" x-model="p.first_name" required
                                           class="flex-1 rounded-xl border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Vārds">
                                    <input :name="`players[${i}][last_name]`" x-model="p.last_name" required
                                           class="flex-1 rounded-xl border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Uzvārds">
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit"
                                class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition">
                            Saglabāt komandu
                        </button>
                        <a href="{{ route('teams.index') }}"
                           class="px-4 py-2.5 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition text-sm font-medium">
                            Atcelt
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
    function teamForm(perTeam, existingPlayers) {
        return {
            perTeam: perTeam,
            players: [],
            init() {
                this.resizePlayers(existingPlayers);
            },
            resizePlayers(preset) {
                const n = parseInt(this.perTeam) || 6;
                const src = preset || this.players;
                this.players = Array.from({ length: n }, (_, i) => ({
                    first_name: src[i]?.first_name ?? '',
                    last_name:  src[i]?.last_name  ?? '',
                }));
            }
        };
    }
    </script>
</x-app-layout>
