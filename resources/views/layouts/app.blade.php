@props(['title' => 'VolleyPass'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'VolleyPass') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/volleyball.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="min-h-screen flex flex-col bg-gray-50 text-gray-900">

    @include('layouts.navigation')

    {{-- Toast notifications --}}
    <div
        x-data="{
            toasts: [],
            add(message, type) {
                const id = Date.now() + Math.random();
                this.toasts.push({ id, message, type, visible: true });
                setTimeout(() => this.remove(id), 4800);
            },
            remove(id) {
                const t = this.toasts.find(t => t.id === id);
                if (t) t.visible = false;
                setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 350);
            }
        }"
        x-init="
            @if(session('success')) $nextTick(() => add(@js(session('success')), 'success')); @endif
            @if(session('error'))   $nextTick(() => add(@js(session('error')),   'error'));   @endif
            @if(session('warning')) $nextTick(() => add(@js(session('warning')), 'warning')); @endif
            @if(session('info'))    $nextTick(() => add(@js(session('info')),    'info'));    @endif
        "
        @toast.window="add($event.detail.message, $event.detail.type ?? 'info')"
        class="fixed top-5 right-5 z-[9999] flex flex-col gap-2.5 w-80 pointer-events-none"
        style="max-width: calc(100vw - 2.5rem);"
        aria-live="polite"
    >
        <template x-for="t in toasts" :key="t.id">
            <div
                x-show="t.visible"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-6 scale-95"
                x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                x-transition:leave-end="opacity-0 translate-x-6 scale-95"
                :class="{
                    'bg-white border-green-400 text-green-800': t.type === 'success',
                    'bg-white border-red-400 text-red-800':   t.type === 'error',
                    'bg-white border-amber-400 text-amber-800': t.type === 'warning',
                    'bg-white border-blue-400 text-blue-800':  t.type === 'info',
                }"
                class="pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-xl shadow-xl border-l-4 border border-gray-100"
            >
                {{-- Icon --}}
                <span class="shrink-0 mt-0.5">
                    <template x-if="t.type === 'success'">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                    <template x-if="t.type === 'error'">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                    <template x-if="t.type === 'warning'">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </template>
                    <template x-if="t.type === 'info'">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                </span>
                <p class="text-sm font-medium flex-1 leading-snug" x-text="t.message"></p>
                <button @click="remove(t.id)" class="shrink-0 text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <main class="flex-grow pt-16">
        {{-- Support both component ($slot) and classic @section/@yield --}}
        @isset($slot)
            {{ $slot }}
        @else
            @yield('content')
        @endisset
    </main>

    <footer class="mt-auto bg-gray-950 border-t border-white/10 text-gray-400 py-7">
        <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center gap-4 text-sm">
            <div class="flex items-center gap-2">
                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-gradient-to-br from-orange-400 to-blue-600">
                    <img src="{{ asset('images/volleyball.png') }}" alt="" class="w-4 h-4">
                </span>
                <span class="font-semibold text-white">VolleyPass</span>
                <span class="text-gray-600">&copy; {{ date('Y') }}</span>
            </div>
            <div class="flex gap-6">
              <a href="{{ route('about') }}" class="hover:text-white transition-colors">Par mums</a>
              <a href="{{ route('contacts') }}" class="hover:text-white transition-colors">Kontakti</a>
            </div>
        </div>
    </footer>

    @stack('scripts')

    {{-- VolleyPass global confirm modal --}}
    <div id="vpConfirmModal"
         class="hidden fixed inset-0 z-[9998] flex items-center justify-center"
         onclick="if(event.target===this) vpConfirmClose()">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6 animate-in fade-in zoom-in-95 duration-150">
            <p id="vpConfirmMessage" class="text-gray-800 dark:text-gray-100 text-base font-medium text-center mb-6"></p>
            <div class="flex gap-3">
                <button onclick="vpConfirmClose()"
                        class="flex-1 px-4 py-2.5 rounded-xl border-2 border-gray-200 text-gray-600 hover:border-gray-300 hover:bg-gray-50 font-semibold text-sm transition">
                    Atcelt
                </button>
                <button id="vpConfirmOk"
                        class="flex-1 px-4 py-2.5 rounded-xl font-semibold text-sm text-white transition shadow-sm">
                    Apstiprināt
                </button>
            </div>
        </div>
    </div>
    <script>
    (function () {
        let _vpCallback = null;

        window.vpConfirm = function (message, onConfirm, options) {
            options = options || {};
            var confirmText  = options.confirmText  || 'Apstiprināt';
            var confirmColor = options.danger ? 'bg-red-500 hover:bg-red-600' : 'bg-blue-600 hover:bg-blue-700';

            document.getElementById('vpConfirmMessage').textContent = message;
            var okBtn = document.getElementById('vpConfirmOk');
            okBtn.textContent = confirmText;
            okBtn.className = 'flex-1 px-4 py-2.5 rounded-xl font-semibold text-sm text-white transition shadow-sm ' + confirmColor;

            _vpCallback = onConfirm;
            document.getElementById('vpConfirmModal').classList.remove('hidden');
        };

        window.vpConfirmClose = function () {
            document.getElementById('vpConfirmModal').classList.add('hidden');
            _vpCallback = null;
        };

        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('vpConfirmOk').addEventListener('click', function () {
                document.getElementById('vpConfirmModal').classList.add('hidden');
                if (typeof _vpCallback === 'function') _vpCallback();
                _vpCallback = null;
            });
        });
    })();
    </script>
</body>
</html>
