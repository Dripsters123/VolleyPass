@php
    use App\Models\Wallet;
    $wallet = auth()->check() ? Wallet::firstWhere('user_id', auth()->id()) : null;
    $navCoins = $wallet ? (int) $wallet->coins : 0;
@endphp

<nav x-data="{ open: false, userMenu: false }"
     class="bg-gray-950 text-white fixed top-0 inset-x-0 z-50 border-b border-white/10">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <a href="{{ auth()->check() ? route('dashboard') : route('home') }}"
               class="flex items-center gap-2.5 shrink-0">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-orange-400 to-blue-600">
                    <img src="{{ asset('images/volleyball.png') }}" alt="" class="w-5 h-5">
                </span>
                <span class="font-bold text-base tracking-tight">VolleyPass</span>
            </a>

            {{-- Primary nav – desktop --}}
            <div class="hidden md:flex items-center gap-1">

                {{-- Mači dropdown --}}
                <div class="relative" x-data="{ matchMenu: false }">
                    <button @click="matchMenu = !matchMenu" @click.outside="matchMenu = false"
                            class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                  d="M8 21h8M12 17v4M5 3h14l-1 8a6 6 0 01-12 0L5 3z"/>
                        </svg>
                        Mači
                        <svg class="w-3 h-3 ml-0.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="matchMenu" x-transition
                         class="absolute left-0 mt-2 w-52 bg-gray-900 border border-white/10 rounded-xl shadow-2xl py-1 z-50">
                        <a href="{{ route('local.matches.index') }}"
                           class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            Visi mači
                        </a>
                        @auth
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('admin.matches.create') }}"
                                   class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Izveidot maču
                                </a>
                            @else
                                <a href="{{ route('match_requests.create') }}"
                                   class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Pieteikt pieprasījumu
                                </a>
                                <a href="{{ route('match_requests.my') }}"
                                   class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    Mani pieprasījumi
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>

                {{-- Veikals dropdown --}}
                <div class="relative" x-data="{ shopMenu: false }">
                    <button @click="shopMenu = !shopMenu" @click.outside="shopMenu = false"
                            class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                  d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0"/>
                        </svg>
                        Veikals
                        <svg class="w-3 h-3 ml-0.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="shopMenu" x-transition
                         class="absolute left-0 mt-2 w-52 bg-gray-900 border border-white/10 rounded-xl shadow-2xl py-1 z-50">
                        <a href="{{ route('products.index') }}"
                           class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            Visi produkti
                        </a>
                        @auth
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('products.create') }}"
                                   class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Pievienot produktu
                                </a>
                            @else
                                <a href="{{ route('product_requests.create') }}"
                                   class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Pieteikt pieprasījumu
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>

                <a href="{{ route('tickets.index') }}"
                   class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                    {{-- ticket icon --}}
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                              d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                    Biļetes
                </a>

                @auth
                <a href="{{ route('arenas.index') }}"
                   class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                    {{-- building/arena icon --}}
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                              d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v3M12 14v3M16 14v3"/>
                    </svg>
                    Arēnas
                </a>
                @endauth
            </div>

            {{-- Right actions – desktop --}}
            <div class="hidden md:flex items-center gap-2">
                @auth
                    {{-- Coin balance --}}
                    <a href="{{ route('wallet.show') }}"
                       title="Monētu atlikums"
                       class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                        <svg class="w-4 h-4 text-yellow-400 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18a8 8 0 110-16 8 8 0 010 16zm.75-11.25h-1.5v-1.5h1.5v1.5zm0 7.5h-1.5v-6h1.5v6z"/>
                        </svg>
                        <span class="text-xs font-bold">{{ number_format($navCoins) }}</span>
                    </a>

                    {{-- Admin inbox (admin only) --}}
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.match_requests.inbox') }}"
                       title="Admin Inbox"
                       class="flex items-center justify-center w-9 h-9 rounded-lg text-gray-400 hover:text-white hover:bg-white/8 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4m4 0l2 2 4-4"/>
                        </svg>
                    </a>
                    @endif

                    {{-- User avatar / dropdown --}}
                    <div class="relative" x-data="{ userMenu: false }">
                        <button @click="userMenu = !userMenu" @click.outside="userMenu = false"
                                class="flex items-center justify-center w-9 h-9 rounded-lg bg-gradient-to-br from-orange-400 to-blue-600 text-white font-semibold text-sm hover:opacity-90 transition">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </button>
                        <div x-show="userMenu" x-transition
                             class="absolute right-0 mt-2 w-52 bg-gray-900 border border-white/10 rounded-xl shadow-2xl py-1 text-sm">
                            <div class="px-4 py-2.5 border-b border-white/10">
                                <p class="font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                                <p class="text-gray-400 text-xs truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}"
                               class="flex items-center gap-2.5 px-4 py-2 text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Profils
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="w-full flex items-center gap-2.5 px-4 py-2 text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Iziet
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}"
                       class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white hover:bg-white/8 rounded-lg transition-colors">
                        Ieiet
                    </a>
                    <a href="{{ route('register') }}"
                       class="px-4 py-2 text-sm font-semibold bg-gradient-to-r from-orange-500 to-blue-600 hover:opacity-90 rounded-lg transition-opacity">
                        Reģistrēties
                    </a>
                @endguest
            </div>

            {{-- Mobile hamburger --}}
            <button @click="open = !open"
                    class="md:hidden flex items-center justify-center w-9 h-9 rounded-lg text-gray-400 hover:text-white hover:bg-white/8 transition-colors">
                <svg x-show="!open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="open" x-transition class="md:hidden border-t border-white/10 bg-gray-950 pb-4">
        <div class="px-4 pt-3 space-y-1">
            <a href="{{ route('local.matches.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                          d="M8 21h8M12 17v4M5 3h14l-1 8a6 6 0 01-12 0L5 3z"/>
                </svg>
                Mači
            </a>
            <a href="{{ route('products.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                          d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0"/>
                </svg>
                Veikals
            </a>
            <a href="{{ route('tickets.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                          d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
                Biļetes
            </a>
            @auth
            <a href="{{ route('arenas.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                          d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v3M12 14v3M16 14v3"/>
                </svg>
                Arēnas
            </a>
            <a href="{{ route('wallet.show') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18a8 8 0 110-16 8 8 0 010 16zm.75-11.25h-1.5v-1.5h1.5v1.5zm0 7.5h-1.5v-6h1.5v6z"/>
                </svg>
                Monētas
                <span class="ml-auto text-xs font-bold text-yellow-400">{{ number_format($navCoins) }}</span>
            </a>
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.matches.create') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v16m8-8H4"/>
                    </svg>
                    Izveidot maču
                </a>
                <a href="{{ route('products.create') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v16m8-8H4"/>
                    </svg>
                    Pievienot produktu
                </a>
                <a href="{{ route('admin.match_requests.inbox') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4m4 0l2 2 4-4"/>
                    </svg>
                    Admin Inbox
                </a>
            @else
                <a href="{{ route('match_requests.create') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v16m8-8H4"/>
                    </svg>
                    Pieteikt pieprasījumu
                </a>
                <a href="{{ route('product_requests.create') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v16m8-8H4"/>
                    </svg>
                    Pieteikt produktu
                </a>
            @endif
            <a href="{{ route('profile.edit') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Profils
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Iziet
                </button>
            </form>
            @else
            <div class="pt-2 flex flex-col gap-2">
                <a href="{{ route('login') }}"
                   class="block text-center px-4 py-2.5 rounded-lg text-sm font-medium border border-white/20 text-gray-300 hover:text-white hover:border-white/40 transition-colors">
                    Ieiet
                </a>
                <a href="{{ route('register') }}"
                   class="block text-center px-4 py-2.5 rounded-lg text-sm font-semibold bg-gradient-to-r from-orange-500 to-blue-600 hover:opacity-90 transition-opacity">
                    Reģistrēties
                </a>
            </div>
            @endauth
        </div>
    </div>
</nav>
