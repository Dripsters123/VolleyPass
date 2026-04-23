<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.match_requests.inbox') }}"
                   class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Produkta pieprasījums #{{ $productRequest->id }}
                </h2>
            </div>
            @php
                $statusMap = [
                    'pending'   => ['label' => 'Gaida', 'class' => 'bg-yellow-100 text-yellow-800 border-yellow-200'],
                    'reviewing' => ['label' => 'Tiek izskatīts', 'class' => 'bg-blue-100 text-blue-800 border-blue-200'],
                    'approved'  => ['label' => 'Apstiprināts', 'class' => 'bg-green-100 text-green-800 border-green-200'],
                    'rejected'  => ['label' => 'Noraidīts', 'class' => 'bg-red-100 text-red-800 border-red-200'],
                ];
                $st = $statusMap[$productRequest->status ?? 'pending'] ?? $statusMap['pending'];
            @endphp
            <span class="px-3 py-1 rounded-full text-sm font-semibold border {{ $st['class'] }}">{{ $st['label'] }}</span>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm">{{ session('error') }}</div>
        @endif

        {{-- Main info card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex flex-col md:flex-row gap-0">
                @if($productRequest->image_path)
                    <div class="md:w-64 flex-shrink-0 bg-gray-50 flex items-center justify-center p-6 border-b md:border-b-0 md:border-r border-gray-100">
                        <img src="{{ asset('storage/' . $productRequest->image_path) }}"
                             class="max-h-52 max-w-full rounded-xl object-contain shadow-sm" alt="{{ $productRequest->title }}">
                    </div>
                @endif
                <div class="flex-1 p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $productRequest->title ?? '—' }}</h3>
                    <p class="text-2xl font-bold text-blue-600 mb-4">€{{ number_format($productRequest->price ?? 0, 2) }}</p>

                    @if($productRequest->description)
                        <p class="text-gray-600 text-sm leading-relaxed mb-4">{{ $productRequest->description }}</p>
                    @endif

                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="text-gray-400">Kategorija</span>
                            <p class="font-medium text-gray-900">{{ $productRequest->category ?? '—' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-400">Iesniegts</span>
                            <p class="font-medium text-gray-900">
                                {{ $productRequest->created_at ? $productRequest->created_at->format('d.m.Y H:i') : '—' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Requester card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Iesniedzējs</h4>
            <div class="flex items-center gap-3">
                @if(optional($productRequest->user)->avatar)
                    <img src="{{ Storage::url($productRequest->user->avatar) }}"
                         class="w-10 h-10 rounded-full object-cover border" alt="">
                @else
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                        <span class="text-white text-sm font-bold">
                            {{ strtoupper(substr(optional($productRequest->user)->name ?? '?', 0, 1)) }}
                        </span>
                    </div>
                @endif
                <div>
                    <p class="font-semibold text-gray-900">{{ optional($productRequest->user)->name ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ optional($productRequest->user)->email ?? '' }}</p>
                </div>
            </div>
        </div>

        {{-- Admin actions --}}
        @if(in_array($productRequest->status, ['pending', 'reviewing']))
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4" x-data="{ rejectOpen: false }">
            <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Darbības</h4>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.product_requests.edit', $productRequest) }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition shadow-sm text-sm">
                    ✓ Apstiprināt / Rediģēt
                </a>

                @if($productRequest->status !== 'reviewing')
                <form method="POST" action="{{ route('admin.product_requests.review', $productRequest) }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition shadow-sm text-sm">
                        👁 Atzīmēt kā "Tiek izskatīts"
                    </button>
                </form>
                @endif

                <button @click="rejectOpen = !rejectOpen"
                        class="inline-flex items-center gap-2 px-5 py-2.5 border border-red-200 hover:bg-red-50 text-red-600 font-medium rounded-xl transition text-sm">
                    ✕ Noraidīt
                </button>
            </div>

            <div x-show="rejectOpen" x-transition class="mt-2">
                <form method="POST" action="{{ route('admin.product_requests.reject', $productRequest) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Noraidījuma iemesls (nav obligāts)</label>
                        <textarea name="rejection_reason" rows="3"
                                  class="w-full rounded-xl border-gray-300 text-sm focus:ring-red-500 focus:border-red-500"
                                  placeholder="Paskaidrojiet noraidījuma iemeslu…"></textarea>
                    </div>
                    <button type="submit"
                            class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl transition text-sm">
                        Apstiprināt noraidījumu
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>
</x-app-layout>

