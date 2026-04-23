<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Manas biļetes</h2>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 py-8">

        @if($tickets->isEmpty())
            <div class="text-center py-20">
                <div class="text-5xl mb-4">🎟️</div>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Nav nopirktu biļešu</h3>
                <p class="text-gray-500 text-sm">Iegādājieties biļetes uz kādu maču!</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($tickets as $ticket)
                    @php
                        $statusMap = [
                            'paid'      => ['label' => 'Apmaksāta', 'class' => 'bg-green-100 text-green-800'],
                            'pending'   => ['label' => 'Gaida', 'class' => 'bg-yellow-100 text-yellow-800'],
                            'cancelled' => ['label' => 'Atcelta', 'class' => 'bg-red-100 text-red-800'],
                        ];
                        $st = $statusMap[$ticket->status] ?? ['label' => ucfirst($ticket->status), 'class' => 'bg-gray-100 text-gray-700'];
                    @endphp
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                        <div class="flex flex-col sm:flex-row">
                            {{-- Left accent stripe --}}
                            <div class="w-full sm:w-1.5 bg-gradient-to-b from-blue-500 to-indigo-600 flex-shrink-0"></div>

                            <div class="flex-1 p-5">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <h3 class="font-bold text-gray-900 dark:text-white text-base">
                                            @if($ticket->event)
                                                <a href="{{ route('volleyball.show', $ticket->event->id) }}"
                                                   class="hover:text-blue-600 dark:hover:text-blue-400 transition">
                                                    {{ $ticket->event->name }}
                                                </a>
                                            @else
                                                Ārējs mačs #{{ $ticket->event_id }}
                                            @endif
                                        </h3>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $ticket->ticket_type }} · nopirkts {{ $ticket->created_at->timezone('Europe/Riga')->format('d.m.Y H:i') }}</p>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $st['class'] }}">{{ $st['label'] }}</span>
                                </div>

                                <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                                        <div class="text-xs text-gray-400 mb-0.5">Daudzums</div>
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $ticket->quantity }}</div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                                        <div class="text-xs text-gray-400 mb-0.5">Summa</div>
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $ticket->amount_paid }} {{ strtoupper($ticket->currency) }}</div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 col-span-2">
                                        <div class="text-xs text-gray-400 mb-0.5">Sēdvietas</div>
                                        @if($ticket->seats->isNotEmpty())
                                            @foreach($ticket->seats as $seat)
                                                <div class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                                    {{ $seat->side ?? 'Tribīne' }} — R{{ $seat->row }}, V{{ $seat->number }}
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-xs text-gray-400">Nav sēdvietu</div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Coin reward --}}
                                <div class="mt-4 flex items-center justify-end">
                                    @if(!$ticket->coin_reward_claimed)
                                        <form action="{{ route('tickets.claim', $ticket->id) }}" method="POST"
                                              onsubmit="return confirm('Apstiprināt monētu pieprasījumu?');">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-yellow-900 text-sm font-semibold rounded-xl transition shadow-sm">
                                                🪙 Saņemt {{ $ticket->quantity * 50 }} monētas
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs font-medium rounded-xl">
                                            ✓ Monētas saņemtas
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</x-app-layout>