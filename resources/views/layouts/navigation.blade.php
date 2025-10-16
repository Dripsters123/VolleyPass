@php
    use App\Models\Wallet;

    $wallet = auth()->check() ? Wallet::firstWhere('user_id', auth()->id()) : null;
@endphp

<nav x-data="{ open: false, matchesDropdown: false, marketDropdown: false, userDropdown: false }" 
     class="bg-gradient-to-r from-orange-400 to-blue-600 text-white fixed top-0 inset-x-0 z-50 shadow-md">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">

            <div class="flex items-center">
                <button @click="open = !open" class="sm:hidden p-2 rounded-md bg-white/20 hover:bg-white/30 mr-2 focus:outline-none">
                    <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="flex items-center space-x-2">
                    <img src="{{ asset('images/volleyball.png') }}" alt="VolleyPass" class="h-8 w-8 rounded-md bg-white/40 p-1">
                    <span class="font-semibold text-lg">VolleyPass</span>
                </a>
            </div>

            <div class="hidden sm:flex items-center space-x-6">

                <div class="relative" @mouseenter="matchesDropdown = true" @mouseleave="matchesDropdown = false">
                    <button class="px-3 py-1 rounded-md hover:bg-white/20 flex items-center text-sm">
                        <span>Mači</span>
                        <svg class="ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 01-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                    <div x-show="matchesDropdown" x-transition class="absolute left-0 mt-2 w-56 bg-white rounded-md shadow-lg text-blue-900 z-50">
                        <a href="{{ route('local.matches.index') }}" class="block px-4 py-2 hover:bg-gray-100">Volejbola mači</a>
                        <a href="{{ route('calendar.index') }}" class="block px-4 py-2 hover:bg.gray-100">Kalendārs</a>
                        @auth
                            <a href="{{ route('match_requests.create') }}" class="block px-4 py-2 hover:bg-gray-100">Izveidot pieprasījumu</a>
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('admin.matches.create') }}" class="block px-4 py-2 font-semibold text-blue-700 hover:bg-gray-100">Izveidot maču</a>
                            @endif
                        @endauth
                    </div>
                </div>

                <div class="relative" @mouseenter="marketDropdown = true" @mouseleave="marketDropdown = false">
                    <button class="px-3 py-1 rounded-md hover:bg-white/20 flex items-center text-sm">
                        <span>Preces</span>
                        <svg class="ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 01-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                    <div x-show="marketDropdown" x-transition class="absolute left-0 mt-2 w-56 bg-white rounded-md shadow-lg text-blue-900 z-50">
                        <a href="{{ route('products.index') }}" class="block px-4 py-2 hover:bg-gray-100">Skatīt produktus</a>
                        @auth
                            <a href="{{ route('product_requests.create') }}" class="block px-4 py-2 hover:bg-gray-100">Pieteikt pieprasījumu</a>
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('products.create') }}" class="block px-4 py-2 font-semibold text-blue-700 hover:bg-gray-100">Pārdot produktu</a>
                            @endif
                        @endauth
                    </div>
                </div>

                <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="px-3 py-1 rounded-md hover:bg-white/20 text-sm">Sākums</a>

                @auth
                    <a href="{{ route('tickets.index') }}" class="px-3 py-1 border rounded-md hover:bg-white/10 text-sm">Manas biļetes</a>
                    <a href="{{ route('predictions.index') }}" class="px-3 py-1 border rounded-md hover:bg-white/10 text-sm">Prognozes</a>
                    <a href="{{ route('match_requests.my') }}" class="px-3 py-1 border rounded-md hover:bg-white/10 text-sm">Mani pieprasījumi</a>

                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.match_requests.inbox') }}" 
                          class="px-3 py-1 border rounded-md hover:bg-white/10 text-sm">
                            Admin Inbox
                        </a>
                    @endif
                @endauth

                @guest
                    <a href="{{ route('login') }}" class="px-3 py-1 border rounded-md hover:bg-white/10 text-sm">Ieiet</a>
                    <a href="{{ route('register') }}" class="px-3 py-1 bg-white/20 rounded-md hover:bg-white/30 text-sm">Reģistrēties</a>
                @endguest

                @auth
                    <div class="relative">
                        <button @click="userDropdown = !userDropdown" class="flex items-center px-3 py-2 rounded-md bg-white/20 hover:bg-white/30 text-sm">
                            <span class="mr-3">{{ Auth::user()->name }}</span>
                            <span class="ml-2 font-semibold">{{ $wallet ? intval($wallet->coins) : 0 }} ⚪</span>
                            <svg class="ml-2 w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 01-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div x-show="userDropdown" x-transition @click.away="userDropdown = false"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg text-blue-900 z-50">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-100">Profils</a>
                            <a href="{{ route('wallet.show') }}" class="block px-4 py-2 hover:bg-gray-100">Atlaides kartes</a>

                            <!-- ADDED: Mani pieprasījumi (user dropdown) -->
                            <a href="{{ route('match_requests.my') }}" class="block px-4 py-2 hover:bg-gray-100">Mani pieprasījumi</a>

                            <a href="{{ route('products.index') }}?mine=1" class="block px-4 py-2 hover:bg-gray-100">Mani produkti</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-100">Atslēgties</button>
                            </form>
                        </div>
                    </div>
                @endauth

            </div>
        </div>
    </div>

    <div x-show="open" x-transition class="sm:hidden bg-white text-blue-900 shadow-lg">
        <div class="p-3 space-y-2">
            <a href="{{ route('local.matches.index') }}" class="block px-3 py-2 hover:bg-gray-100">Volejbola mači</a>
            <a href="{{ route('calendar.index') }}" class="block px-3 py-2 hover:bg-gray-100">Kalendārs</a>
            <a href="{{ route('products.index') }}" class="block px-3 py-2 hover:bg-gray-100">Skatīt produktus</a>

            @auth
                <a href="{{ route('wallet.show') }}" class="block px-3 py-2 hover:bg-gray-100">Atlaides kartes</a>
                <a href="{{ route('tickets.index') }}" class="block px-3 py-2 hover:bg-gray-100">Manas biļetes</a>
                <a href="{{ route('predictions.index') }}" class="block px-3 py-2 hover:bg-gray-100">Prognozes</a>
                <a href="{{ route('match_requests.my') }}" class="block px-3 py-2 hover:bg-gray-100">Mani pieprasījumi</a>

                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.match_requests.inbox') }}" 
                      class="block px-3 py-2 hover:bg-gray-100">
                       Admin Inbox
                    </a>
                @endif
            @endauth

            @guest
                <a href="{{ route('login') }}" class="block px-3 py-2 hover:bg-gray-100">Ieiet</a>
                <a href="{{ route('register') }}" class="block px-3 py-2 hover:bg-gray-100">Reģistrēties</a>
            @endguest
        </div>
    </div>
</nav>
