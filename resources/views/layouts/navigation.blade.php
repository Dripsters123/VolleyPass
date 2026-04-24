@php
    use App\Models\Wallet;
    use Illuminate\Support\Facades\Storage;
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
                        <a href="{{ route('calendar.index') }}"
                           class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Kalendārs
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
                                <div class="border-t border-white/10 my-1"></div>
                                <a href="{{ route('teams.index') }}"
                                   class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Manas komandas
                                </a>
                                <a href="{{ route('teams.create') }}"
                                   class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Jauna komanda
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
                            <a href="{{ route('products.my') }}"
                               class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                </svg>
                                Mani produkti
                            </a>
                        @endauth
                    </div>
                </div>

                @auth
                <a href="{{ route('tickets.index') }}"
                   class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                    {{-- ticket icon --}}
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                              d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                    Biļetes
                </a>
                <a href="{{ route('orders.index') }}"
                   class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                              d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    Mani pasūtījūmi
                </a>
                @endauth

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
                    @php $navUnread = auth()->user()->unreadNotifications->count(); @endphp

                    {{-- Coin balance --}}
                    <a href="{{ route('wallet.show') }}"
                       title="Monētu atlikums"
                       class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                        <svg class="w-4 h-4 text-yellow-400 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18a8 8 0 110-16 8 8 0 010 16zm.75-11.25h-1.5v-1.5h1.5v1.5zm0 7.5h-1.5v-6h1.5v6z"/>
                        </svg>
                        <span class="text-xs font-bold">{{ number_format($navCoins) }}</span>
                    </a>

                    {{-- Notifications bell --}}
                    <div class="relative" x-data="{ notifMenu: false }">
                        <button @click="notifMenu = !notifMenu" @click.outside="notifMenu = false"
                                class="relative flex items-center justify-center w-9 h-9 rounded-lg text-gray-400 hover:text-white hover:bg-white/8 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if($navUnread > 0)
                                <span class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                                    {{ $navUnread > 9 ? '9+' : $navUnread }}
                                </span>
                            @endif
                        </button>
                        <div x-show="notifMenu" x-transition
                             class="absolute right-0 mt-2 w-80 bg-gray-900 border border-white/10 rounded-xl shadow-2xl text-sm z-50">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
                                <span class="font-semibold text-white">Paziņojumi</span>
                                @if($navUnread > 0)
                                    <form action="{{ route('notifications.readAll') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs text-blue-400 hover:text-blue-300">Atzīmēt visus</button>
                                    </form>
                                @endif
                            </div>
                            @php $recentNotifs = auth()->user()->notifications()->latest()->take(5)->get(); @endphp
                            @if($recentNotifs->isEmpty())
                                <div class="px-4 py-6 text-center text-gray-500 text-xs">Nav paziņojumu</div>
                            @else
                                @foreach($recentNotifs as $notif)
                                    @php $nd = $notif->data; $nRead = !is_null($notif->read_at); @endphp
                                    <div class="flex items-start gap-3 px-4 py-3 {{ $nRead ? '' : 'bg-white/5' }} hover:bg-white/8 transition-colors border-b border-white/5 last:border-0">
                                        <span class="flex-shrink-0 text-base mt-0.5">
                                            @if(($nd['new_status'] ?? '') === 'accepted') ✅
                                            @elseif(($nd['new_status'] ?? '') === 'rejected') ❌
                                            @elseif(($nd['new_status'] ?? '') === 'reviewing') 👁
                                            @else 📨
                                            @endif
                                        </span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-gray-200 text-xs leading-snug truncate">{{ $nd['message'] ?? '—' }}</p>
                                            <p class="text-gray-500 text-[10px] mt-0.5">{{ $notif->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                            <div class="px-4 py-2.5 border-t border-white/10">
                                <a href="{{ route('notifications.index') }}" class="text-xs text-blue-400 hover:text-blue-300">Visi paziņojumi →</a>
                            </div>
                        </div>
                    </div>

                    {{-- Admin inbox (admin only) --}}
                    @if(auth()->user()->role === 'admin')
                    @php $pendingCount = \App\Models\MatchRequest::where('status', 'pending')->count() + \App\Models\ProductRequest::where('status', 'pending')->count(); @endphp
                    <a href="{{ route('admin.match_requests.inbox') }}"
                       title="Admin Inbox"
                       class="relative flex items-center justify-center w-9 h-9 rounded-lg text-gray-400 hover:text-white hover:bg-white/8 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4m4 0l2 2 4-4"/>
                        </svg>
                        @if($pendingCount > 0)
                            <span class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-orange-500 text-[10px] font-bold text-white">
                                {{ $pendingCount > 9 ? '9+' : $pendingCount }}
                            </span>
                        @endif
                    </a>
                    @endif

                    {{-- User avatar / dropdown --}}
                    <div class="relative" x-data="{ userMenu: false }">
                        <button @click="userMenu = !userMenu" @click.outside="userMenu = false"
                                class="flex items-center justify-center w-9 h-9 rounded-lg overflow-hidden bg-gradient-to-br from-orange-400 to-blue-600 text-white font-semibold text-sm hover:opacity-90 transition">
                            @if(auth()->user()->avatar)
                                <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            @endif
                        </button>
                        <div x-show="userMenu" x-transition
                             class="absolute right-0 mt-2 w-52 bg-gray-900 border border-white/10 rounded-xl shadow-2xl py-1 text-sm">
                            <div class="px-4 py-2.5 border-b border-white/10 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg overflow-hidden bg-gradient-to-br from-orange-400 to-blue-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                    @if(auth()->user()->avatar)
                                        <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-gray-400 text-xs truncate">{{ auth()->user()->email }}</p>
                                </div>
                            </div>
                            <a href="{{ route('profile.edit') }}"
                               class="flex items-center gap-2.5 px-4 py-2 text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Profils
                            </a>
                            <a href="{{ route('notifications.index') }}"
                               class="flex items-center gap-2.5 px-4 py-2 text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                Paziņojumi
                                @if($navUnread > 0)
                                    <span class="ml-auto bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $navUnread }}</span>
                                @endif
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
            <a href="{{ route('calendar.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Kalendārs
            </a>
            <a href="{{ route('products.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                          d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0"/>
                </svg>
                Veikals
            </a>
            @auth
            <a href="{{ route('tickets.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                          d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
                Biļetes
            </a>
            <a href="{{ route('orders.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                Mani pasūtījūmi
            </a>
            <a href="{{ route('products.my') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                </svg>
                Mani produkti
            </a>
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
                <a href="{{ route('match_requests.my') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Mani pieprasījumi
                </a>
                <a href="{{ route('teams.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Manas komandas
                </a>
                <a href="{{ route('teams.create') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/8 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v16m8-8H4"/>
                    </svg>
                    Jauna komanda
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
