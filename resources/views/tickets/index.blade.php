<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
           Manas biļetes
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Desktop Table --}}
            <div class="hidden md:block bg-white shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Mačš</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Tips</th>
                            <th class="px-4 py-2 text-center text-sm font-semibold text-gray-700">Daudzums</th>
                            <th class="px-4 py-2 text-center text-sm font-semibold text-gray-700">Summa</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Sēdvietas</th>
                            <th class="px-4 py-2 text-center text-sm font-semibold text-gray-700">Statuss</th>
                            <th class="px-4 py-2 text-center text-sm font-semibold text-gray-700">Nopirkts</th>
                            <th class="px-4 py-2 text-center text-sm font-semibold text-gray-700">Monētas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($tickets as $ticket)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-2">
                                    @if($ticket->event)
                                        <a href="{{ route('volleyball.show', $ticket->event->id) }}" class="text-blue-600 hover:underline">{{ $ticket->event->name }}</a>
                                    @else
                                        External Match #{{ $ticket->event_id }}
                                    @endif
                                </td>
                                <td class="px-4 py-2">{{ $ticket->ticket_type }}</td>
                                <td class="px-4 py-2 text-center">{{ $ticket->quantity }}</td>
                                <td class="px-4 py-2 text-center">{{ $ticket->amount_paid }} {{ strtoupper($ticket->currency) }}</td>
                                <td class="px-4 py-2">
                                    @if($ticket->seats->isNotEmpty())
                                        @foreach($ticket->seats as $seat)
                                            <div class="text-sm text-gray-600">
                                                {{ $seat->side ?? 'Nezināma tribīne' }} — R{{ $seat->row }}, V{{ $seat->number }}
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="text-gray-500 text-sm">N/A</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <span class="inline-block px-2 py-1 rounded-full text-sm font-medium
                                        {{ $ticket->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $ticket->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $ticket->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                    ">
                                        {{ ucfirst($ticket->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-center text-sm text-gray-600">{{ $ticket->created_at->timezone('Europe/Riga')->format('d.m.Y H:i') }}</td>
                                <td class="px-4 py-2 text-center">
                                    @if(!$ticket->coin_reward_claimed)
                                        <form action="{{ route('tickets.claim', $ticket->id) }}" method="POST" onsubmit="return confirm('Apstiprināt monētu pieprasījumu?');">
                                            @csrf
                                            <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm">
                                                Saņemt {{ $ticket->quantity * 50 }} coins
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-500 text-sm">Monētas saņemtas</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                                    Nav nopirktu biļešu!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="md:hidden space-y-4">
                @forelse($tickets as $ticket)
                    <div class="bg-white shadow-md rounded-lg p-4 hover:shadow-lg transition">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-semibold text-blue-600">
                                    @if($ticket->event)
                                        <a href="{{ route('volleyball.show', $ticket->event->id) }}" class="hover:underline">{{ $ticket->event->name }}</a>
                                    @else
                                        External Match #{{ $ticket->event_id }}
                                    @endif
                                </h3>
                                <span class="text-sm text-gray-600">{{ $ticket->ticket_type }}</span>
                            </div>
                            <span class="inline-block px-2 py-1 rounded-full text-sm font-medium
                                {{ $ticket->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $ticket->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $ticket->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                            ">
                                {{ ucfirst($ticket->status) }}
                            </span>
                        </div>

                        <div class="mt-2 text-sm text-gray-600 space-y-1">
                            <div>Quantity: {{ $ticket->quantity }}</div>
                            <div>Paid: {{ $ticket->amount_paid }} {{ strtoupper($ticket->currency) }}</div>
                            <div>
                                Seats:
                                @if($ticket->seats->isNotEmpty())
                                    @foreach($ticket->seats as $seat)
                                        <div>{{ $seat->side ?? 'Nezināma tribīne' }} — R{{ $seat->row }}, V{{ $seat->number }}</div>
                                    @endforeach
                                @else
                                    N/A
                                @endif
                            </div>
                            <div>Purchased: {{ $ticket->created_at->timezone('Europe/Riga')->format('d.m.Y H:i') }}</div>
                        </div>

                        <div class="mt-3 text-right">
                            @if(!$ticket->coin_reward_claimed)
                                <form action="{{ route('tickets.claim', $ticket->id) }}" method="POST" onsubmit="return confirm('Apstiprināt monētu pieprasījumu?');">
                                    @csrf
                                    <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm">
                                        Saņemt {{ $ticket->quantity * 50 }} coins
                                    </button>
                                </form>
                            @else
                                <span class="text-gray-500 text-sm">Monētas saņemtas</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-500 py-6">Nav nopirktu biļešu!</div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
