<nav x-data="{ open: false }" class="bg-gradient-to-r from-orange-400 to-blue-600 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="h-16 flex items-center justify-between">
            <div class="flex items-center">
                <!-- Mobile Hamburger -->
                <button @click="open = !open"
                        class="sm:hidden p-2 rounded-md bg-white/20 hover:bg-white/30 mr-2"
                        aria-label="Toggle menu">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path :class="{ 'hidden': open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{ 'hidden': !open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center space-x-3">
                    <img src="{{ asset('images/volleyball.png') }}" alt="VolleyPass" class="h-8 w-8 rounded-md bg-white/40 p-1">
                    <span class="font-semibold text-lg">VolleyPass</span>
                </a>
            </div>

            <!-- Desktop Links -->
            <div class="hidden sm:flex items-center space-x-6">
                <a href="{{ route('home') }}" 
                   class="px-3 py-1 rounded-md hover:underline {{ request()->routeIs('home') ? 'underline font-semibold' : '' }}">Sākumlapa</a>
                <a href="{{ route('about') }}" 
                   class="px-3 py-1 rounded-md hover:underline {{ request()->routeIs('about') ? 'underline font-semibold' : '' }}">Par mums</a>
                <a href="{{ route('contacts') }}" 
                   class="px-3 py-1 rounded-md hover:underline {{ request()->routeIs('contacts') ? 'underline font-semibold' : '' }}">Kontakti</a>
                <a href="{{ route('volleyball.index') }}" 
                   class="px-3 py-1 rounded-md hover:underline {{ request()->routeIs('volleyball.index') ? 'underline font-semibold' : '' }}">Maču pārskats</a>
                <a href="{{ route('calendar.index') }}" 
                   class="px-3 py-1 rounded-md hover:underline {{ request()->routeIs('calendar.index') ? 'underline font-semibold' : '' }}">Kalendārs</a>

                @auth
                    <a href="{{ route('dashboard') }}" class="px-3 py-1 border rounded-md hover:bg-white/10">Panelis</a>
                    <a href="{{ route('tickets.index') }}" class="px-3 py-1 border rounded-md hover:bg-white/10">My Tickets</a>

                    <!-- Dropdown -->
                    <div class="relative ml-4" x-data="{ dropdownOpen: false }">
                        <button @click="dropdownOpen = !dropdownOpen" class="flex items-center px-3 py-2 rounded-md bg-white/20 hover:bg-white/30">
                            <span>{{ Auth::user()->name }}</span>
                            <svg class="ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-transition
                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg text-blue-900 z-50">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-100">Profile</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-100">Log Out</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-1 rounded-md hover:bg-white/10">Pieslēgties</a>
                    <a href="{{ route('register') }}" class="ml-2 px-4 py-2 bg-white text-blue-700 rounded-md hover:bg-white/90">Reģistrēties</a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="open" x-transition class="sm:hidden bg-white/90 text-blue-900 border-t">
        <div class="px-4 py-3 space-y-2">
            <a href="{{ route('home') }}" class="block px-3 py-1 rounded-md hover:bg-blue-100">Sākumlapa</a>
            <a href="{{ route('about') }}" class="block px-3 py-1 rounded-md hover:bg-blue-100">Par mums</a>
            <a href="{{ route('contacts') }}" class="block px-3 py-1 rounded-md hover:bg-blue-100">Kontakti</a>
            <a href="{{ route('volleyball.index') }}" class="block px-3 py-1 rounded-md hover:bg-blue-100">Maču pārskats</a>
            <a href="{{ route('calendar.index') }}" class="block px-3 py-1 rounded-md hover:bg-blue-100">Kalendārs</a>

            @auth
                <a href="{{ route('dashboard') }}" class="block px-3 py-1 rounded-md hover:bg-blue-100">Panelis</a>
                <a href="{{ route('tickets.index') }}" class="block px-3 py-1 rounded-md hover:bg-blue-100">My Tickets</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-1 rounded-md hover:bg-blue-100">Atslēgties</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block px-3 py-1 rounded-md hover:bg-blue-100">Pieslēgties</a>
                <a href="{{ route('register') }}" class="block px-3 py-1 rounded-md hover:bg-blue-100">Reģistrēties</a>
            @endauth
        </div>
    </div>
</nav>
