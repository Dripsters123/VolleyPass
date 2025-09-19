@props(['title' => 'VolleyPass – Auth'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-800 antialiased">

    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center">
            <a href="{{ route('home') }}" class="flex items-center text-2xl font-bold text-red-600">
                <img src="{{ asset('images/volleyball.png') }}" class="h-8 w-8 mr-2" alt="">
                VolleyPass
            </a>
        </div>
    </nav>

    <main>
        {{ $slot }}
    </main>

    <footer class="py-8"></footer>
</body>
</html>
