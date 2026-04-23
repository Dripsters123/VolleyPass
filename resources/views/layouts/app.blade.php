@props(['title' => 'VolleyPass'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'VolleyPass') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="min-h-screen flex flex-col bg-gray-50 text-gray-900">

    @include('layouts.navigation')

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
</body>
</html>
