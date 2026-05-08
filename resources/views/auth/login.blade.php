<x-app-layout title="VolleyPass – Pieslēgties">

    <div class="min-h-[calc(100vh-4rem)] flex">

        {{-- Left panel – branding (hidden on mobile) --}}
        <div class="hidden lg:flex lg:w-1/2 relative bg-gray-950 flex-col items-center justify-center overflow-hidden">
            {{-- Background image --}}
            <div class="absolute inset-0">
                <img src="/images/slide4.jpg" alt="" class="w-full h-full object-cover opacity-30">
                <div class="absolute inset-0 bg-gradient-to-br from-gray-950/80 via-gray-950/60 to-blue-900/40"></div>
            </div>
            {{-- Glow orbs --}}
            <div class="absolute top-1/4 left-1/4 w-72 h-72 rounded-full bg-orange-500/20 blur-3xl"></div>
            <div class="absolute bottom-1/4 right-1/4 w-72 h-72 rounded-full bg-blue-600/20 blur-3xl"></div>
            {{-- Content --}}
            <div class="relative z-10 text-center px-12">
                <div class="flex items-center justify-center gap-3 mb-8">
                    <span class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-orange-400 to-blue-600">
                        <img src="/images/volleyball.png" alt="" width="28" height="28" loading="lazy" class="w-7 h-7">
                    </span>
                    <span class="text-2xl font-extrabold text-white tracking-tight">VolleyPass</span>
                </div>
                <h2 class="text-3xl font-extrabold text-white leading-tight">
                    Atgriezies<br>
                    <span class="bg-gradient-to-r from-orange-400 to-blue-400 bg-clip-text text-transparent">spēlēs ritmā.</span>
                </h2>
                <p class="mt-4 text-gray-400 text-sm leading-relaxed max-w-xs mx-auto">
                    Pieslēdzies un turpini sekot savām mīcļaīkstākajam komandām.
                </p>
            </div>
        </div>

        {{-- Right panel – form --}}
        <div class="w-full lg:w-1/2 flex items-center justify-center bg-white px-6 py-12">
            <div class="w-full max-w-sm">

                {{-- Mobile logo --}}
                <div class="flex items-center justify-center gap-2 mb-8 lg:hidden">
                    <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-gradient-to-br from-orange-400 to-blue-600">
                        <img src="/images/volleyball.png" alt="" class="w-5 h-5">
                    </span>
                    <span class="text-xl font-extrabold text-gray-900 tracking-tight">VolleyPass</span>
                </div>

                <div class="mb-8">
                    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Pieslēgties</h1>
                    <p class="mt-1 text-sm text-gray-500">Nav konta? <a href="{{ route('register') }}" class="text-blue-600 font-medium hover:underline">Reģistrēties</a></p>
                </div>

                @if (session('status'))
                    <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-700 flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">E-pasts</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                               class="w-full rounded-xl border {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-gray-50' }} px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-colors">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1.5 leading-tight">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Parole</label>
                        <input type="password" name="password" required autocomplete="current-password"
                               class="w-full rounded-xl border {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-gray-50' }} px-4 py-3 text-sm text-gray-900 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-colors">
                        @error('password')
                            <p class="text-red-500 text-xs mt-1.5 leading-tight">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            class="w-full py-3 rounded-xl text-white font-semibold bg-gradient-to-r from-orange-500 to-blue-600 hover:opacity-90 transition-opacity shadow-lg shadow-blue-500/20 text-sm">
                        Pieslēgties
                    </button>
                </form>

            </div>
        </div>
    </div>

</x-app-layout>
