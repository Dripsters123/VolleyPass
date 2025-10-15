<x-app-layout>
    <div class="max-w-7xl mx-auto bg-white shadow rounded p-6 mt-10">
        <h1 class="text-2xl font-bold text-blue-700 mb-4">Visi mači</h1>
        <table class="w-full border">
            <thead>
                <tr class="bg-blue-600 text-white">
                    <th class="p-2 text-left">ID</th>
                    <th class="p-2 text-left">Mājas</th>
                    <th class="p-2 text-left">Viesi</th>
                    <th class="p-2 text-left">Datums</th>
                </tr>
            </thead>
            <tbody>
                @forelse($matches as $match)
                    <tr class="border-t">
                        <td class="p-2">{{ $match->id }}</td>
                        <td class="p-2">{{ $match->home_team }}</td>
                        <td class="p-2">{{ $match->away_team }}</td>
                        <td class="p-2">{{ optional($match->start_time)->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-4 text-center">Nav maču.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">{{ $matches->links() }}</div>
    </div>
</x-app-layout>
