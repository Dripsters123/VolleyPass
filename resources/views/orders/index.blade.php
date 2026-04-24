<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Mani pasūtījumi</h2>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 py-8">

        @if(session('success'))
            <div class="mb-5 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($orders->isEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-16 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <p class="text-gray-500 font-medium">Nav neviena pasūtījuma</p>
                <p class="text-sm text-gray-400 mt-1">Jūs vēl neesat iegādājušies nevienu produktu.</p>
                <a href="{{ route('products.index') }}"
                   class="mt-4 inline-block px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition">
                    Doties uz veikalu
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($orders as $order)
                    @php
                        $statusColor = match($order->status) {
                            'paid'      => 'bg-green-100 text-green-700',
                            'pending'   => 'bg-amber-100 text-amber-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                            default     => 'bg-gray-100 text-gray-600',
                        };
                        $statusLabel = match($order->status) {
                            'paid'      => 'Apmaksāts',
                            'pending'   => 'Gaida maksājumu',
                            'cancelled' => 'Atcelts',
                            default     => ucfirst($order->status),
                        };
                    @endphp
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col sm:flex-row">
                        {{-- Product image --}}
                        <div class="sm:w-36 sm:flex-shrink-0 h-36 sm:h-auto bg-gray-50 dark:bg-gray-700 overflow-hidden">
                            @if($order->product?->image_path)
                                <img src="{{ asset('storage/' . $order->product->image_path) }}"
                                     alt="{{ $order->product->title }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Order details --}}
                        <div class="flex-1 p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-400 mb-0.5">Pasūtījums #{{ $order->id }}</p>
                                <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                                    {{ $order->product?->title ?? 'Produkts nav pieejams' }}
                                </h3>
                                @if($order->product?->category)
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $order->product->category }}</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-2">
                                    {{ \Carbon\Carbon::parse($order->created_at)->format('d.m.Y H:i') }}
                                </p>
                            </div>

                            <div class="flex sm:flex-col items-center sm:items-end gap-3 sm:gap-1 shrink-0">
                                <span class="text-lg font-bold text-blue-700 dark:text-blue-400">
                                    €{{ number_format($order->amount, 2) }}
                                </span>
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>

                            @if($order->product && $order->product->status === 'active')
                                <a href="{{ route('products.show', $order->product) }}"
                                   class="shrink-0 px-4 py-2 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition text-center">
                                    Skatīt
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $orders->links() }}</div>
        @endif
    </div>
</x-app-layout>
