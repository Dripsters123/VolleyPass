<x-app-layout title="VolleyPass – Reģistrēties">

    <div class="min-h-[calc(100vh-4rem)] flex">

        {{-- Left panel – branding (hidden on mobile) --}}
        <div class="hidden lg:flex lg:w-1/2 relative bg-gray-950 flex-col items-center justify-center overflow-hidden">
            <div class="absolute inset-0">
                <img src="/images/slide4.jpg" alt="" class="w-full h-full object-cover opacity-30">
                <div class="absolute inset-0 bg-gradient-to-br from-gray-950/80 via-gray-950/60 to-orange-900/30"></div>
            </div>
            <div class="absolute top-1/3 left-1/4 w-72 h-72 rounded-full bg-blue-600/20 blur-3xl"></div>
            <div class="absolute bottom-1/4 right-1/3 w-64 h-64 rounded-full bg-orange-500/20 blur-3xl"></div>
            <div class="relative z-10 text-center px-12">
                <div class="flex items-center justify-center gap-3 mb-8">
                    <span class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-orange-400 to-blue-600">
                        <img src="/images/volleyball.png" alt="" width="28" height="28" loading="lazy" class="w-7 h-7">
                    </span>
                    <span class="text-2xl font-extrabold text-white tracking-tight">VolleyPass</span>
                </div>
                <h2 class="text-3xl font-extrabold text-white leading-tight">
                    Pievienojies<br>
                    <span class="bg-gradient-to-r from-orange-400 to-blue-400 bg-clip-text text-transparent">volejbola kopienai.</span>
                </h2>
                <p class="mt-4 text-gray-400 text-sm leading-relaxed max-w-xs mx-auto">
                    Reģistrējies, iegādājies biļetes un nopalaīd garam nevienu maču.
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
                    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Izveidot kontu</h1>
                    <p class="mt-1 text-sm text-gray-500">Jau ir konts? <a href="{{ route('login') }}" class="text-blue-600 font-medium hover:underline">Pieslēgties</a></p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Vārds</label>
                        <input type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
                               class="w-full rounded-xl border {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-gray-50' }} px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-colors"
                               placeholder="Jānis Bērziņš">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1.5 leading-tight">{{ $message }}</p>
                        @enderror
                    </div>

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
                        <input type="password" name="password" required autocomplete="new-password"
                               class="w-full rounded-xl border {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-gray-50' }} px-4 py-3 text-sm text-gray-900 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-colors">
                        @error('password')
                            <p class="text-red-500 text-xs mt-1.5 leading-tight">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-400 mt-1.5 leading-snug">
                            Vismaz 12 rakstzīmes – lielie un mazie burti, cipari un simboli.
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Apstiprināt paroli</label>
                        <input type="password" name="password_confirmation" required autocomplete="new-password"
                               class="w-full rounded-xl border {{ $errors->has('password_confirmation') ? 'border-red-400 bg-red-50' : 'border-gray-200 bg-gray-50' }} px-4 py-3 text-sm text-gray-900 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-colors">
                        @error('password_confirmation')
                            <p class="text-red-500 text-xs mt-1.5 leading-tight">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            class="w-full py-3 rounded-xl text-white font-semibold bg-gradient-to-r from-orange-500 to-blue-600 hover:opacity-90 transition-opacity shadow-lg shadow-blue-500/20 text-sm">
                        Izveidot kontu
                    </button>
                </form>

            </div>
        </div>
    </div>

</x-app-layout>
