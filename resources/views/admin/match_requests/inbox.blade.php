<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            My Tickets
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="hidden md:block bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full border border-gray-300">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border text-left">Match</th>
                            <th class="px-4 py-2 border text-left">Type</th>
                            <th class="px-4 py-2 border text-center">Qty</th>
                            <th class="px-4 py-2 border text-right">Amount</th>
                            <th class="px-4 py-2 border text-left">Seat</th>
                            <th class="px-4 py-2 border text-center">Status</th>
                            <th class="px-4 py-2 border text-left">Purchased</th>
                            <th class="px-4 py-2 border text-center">Coins</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr class="text-gray-700 hover:bg-gray-50">
                                <td class="px-4 py-2 border">
                                    @if($ticket->event)
                                        <a href="{{ route('volleyball.show', $ticket->event->id) }}" class="text-blue-600 hover:underline">
                                            {{ $ticket->event->name }}
                                        </a>
                                    @else
                                        External Match #{{ $ticket->event_id }}
                                    @endif
                                </td>
                                <td class="px-4 py-2 border">{{ $ticket->ticket_type }}</td>
                                <td class="px-4 py-2 border text-center">{{ $ticket->quantity }}</td>
                                <td class="px-4 py-2 border text-right">{{ $ticket->amount_paid }} {{ strtoupper($ticket->currency) }}</td>
                                <td class="px-4 py-2 border">
                                    @if($ticket->seats->isNotEmpty())
                                        @foreach($ticket->seats as $seat)
                                            <div>
                                                <span class="font-semibold">{{ $seat->side ?? 'Unknown' }}</span> —
                                                Row {{ $seat->row }}, Seat {{ $seat->number }}
                                            </div>
                                        @endforeach
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-4 py-2 border text-center capitalize">{{ $ticket->status }}</td>
                                <td class="px-4 py-2 border">{{ $ticket->created_at->timezone('Europe/Riga')->format('d.m.Y H:i') }}</td>
                                <td class="px-4 py-2 border text-center">
                                    @if(!$ticket->coin_reward_claimed)
                                        <form action="{{ route('tickets.claim', $ticket->id) }}" method="POST" onsubmit="return confirm('Confirm claiming coins?');">
                                            @csrf
                                            <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
                                                Claim {{ $ticket->quantity * 50 }} ⚪
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-500">Claimed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-4 border text-center text-gray-500">No tickets purchased yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4 px-4">
                    {{ $tickets->links() }}
                </div>
            </div>

            <div class="md:hidden space-y-4">
                @forelse($tickets as $ticket)
                    <div class="bg-white shadow rounded-lg p-4 border hover:bg-gray-50">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-semibold text-gray-700">
                                @if($ticket->event)
                                    <a href="{{ route('volleyball.show', $ticket->event->id) }}" class="text-blue-600 hover:underline">
                                        {{ $ticket->event->name }}
                                    </a>
                                @else
                                    External Match #{{ $ticket->event_id }}
                                @endif
                            </span>
                            <span class="text-gray-500 capitalize">{{ $ticket->status }}</span>
                        </div>

                        <div class="mb-2">
                            <div>Type: {{ $ticket->ticket_type }}</div>
                            <div>Qty: {{ $ticket->quantity }}</div>
                            <div>Amount: {{ $ticket->amount_paid }} {{ strtoupper($ticket->currency) }}</div>
                        </div>

                        <div class="mb-2">
                            <div class="font-semibold">Seats:</div>
                            @if($ticket->seats->isNotEmpty())
                                @foreach($ticket->seats as $seat)
                                    <div>
                                        <span>{{ $seat->side ?? 'Unknown' }}</span> — Row {{ $seat->row }}, Seat {{ $seat->number }}
                                    </div>
                                @endforeach
                            @else
                                <div>N/A</div>
                            @endif
                        </div>

                        <div class="flex justify-between items-center mt-2">
                            <div class="text-gray-500 text-sm">
                                Purchased: {{ $ticket->created_at->timezone('Europe/Riga')->format('d.m.Y H:i') }}
                            </div>
                            <div>
                                @if(!$ticket->coin_reward_claimed)
                                    <form action="{{ route('tickets.claim', $ticket->id) }}" method="POST" onsubmit="return confirm('Confirm claiming coins?');">
                                        @csrf
                                        <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm">
                                            Claim {{ $ticket->quantity * 50 }} ⚪
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-500 text-sm">Claimed</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-500">No tickets purchased yet.</div>
                @endforelse

                <div class="mt-4 px-2">
                    {{ $tickets->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
