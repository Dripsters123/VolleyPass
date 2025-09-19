@props(['title' => 'VolleyPass'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-gray-900">

    <!-- NAV -->
    @include('layouts.navigation')

    <!-- PAGE CONTENT -->
    <main class="flex-grow pt-20">
        {{ $slot }}
    </main>

    <!-- FOOTER -->
    <footer class="mt-auto bg-gray-900 text-gray-300 py-6">
        <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>&copy; {{ date('Y') }} VolleyPass</div>
            <div class="flex gap-4">
                <a href="#" class="hover:text-white">Facebook</a>
                <a href="#" class="hover:text-white">Instagram</a>
                <a href="#" class="hover:text-white">X</a>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
