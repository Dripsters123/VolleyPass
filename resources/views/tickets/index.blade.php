<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            My Tickets
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <table class="min-w-full border border-gray-300">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border">Match</th>
                            <th class="px-4 py-2 border">Type</th>
                            <th class="px-4 py-2 border">Quantity</th>
                            <th class="px-4 py-2 border">Amount Paid</th>
                            <th class="px-4 py-2 border">Seat</th>
                            <th class="px-4 py-2 border">Status</th>
                            <th class="px-4 py-2 border">Purchased At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr class="text-center">
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
                                <td class="px-4 py-2 border">{{ $ticket->quantity }}</td>
                                <td class="px-4 py-2 border">{{ $ticket->amount_paid }} {{ strtoupper($ticket->currency) }}</td>
                                <td class="px-4 py-2 border">{{ $ticket->seat_number ?? 'N/A' }}</td>
                                <td class="px-4 py-2 border capitalize">{{ $ticket->status }}</td>
                                <td class="px-4 py-2 border">
                                    {{ $ticket->created_at->timezone('Europe/Riga')->format('d.m.Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-2 border text-center text-gray-500">
                                    No tickets purchased yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
