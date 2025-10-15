<x-app-layout title="VolleyPass – Pieslēgties">
    <div class="max-w-5xl mx-auto px-6 py-12">
        <div class="grid md:grid-cols-2 gap-8 items-stretch">

            <!-- LEFT: Slideshow -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden h-[500px]">
                <div class="bg-gradient-to-r from-orange-400 to-blue-600 text-white px-6 py-4">
                    <h2 class="text-xl font-bold">VolleyPass</h2>
                </div>
                <div x-data="{
                        active: 0,
                        images: ['/images/slide4.jpg'],
                        next(){ this.active = (this.active + 1) % this.images.length }
                    }"
                     x-init="setInterval(()=>next(), 4000)"
                     class="relative h-[440px]">
                    <template x-for="(image, idx) in images" :key="idx">
                        <div x-show="active===idx" x-transition class="absolute inset-0">
                            <img :src="image" alt="" class="w-full h-full object-cover">
                        </div>
                    </template>
                </div>
            </div>

            <!-- RIGHT: Login Form -->
            <div class="bg-white rounded-2xl shadow-lg flex items-center justify-center h-[500px]">
                <div class="w-full max-w-sm p-6">
                    <h2 class="text-2xl font-bold text-center mb-6">Pieslēgties</h2>

                    {{-- show auth / validation errors --}}
                    @if ($errors->any())
                        <div class="mb-4 p-3 rounded bg-red-50 border border-red-200 text-sm text-red-700">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="mb-4 p-3 rounded bg-green-50 border border-green-200 text-sm text-green-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium mb-1">E-pasts</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Parole</label>
                            <input type="password" name="password" required
                                   class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <button type="submit" class="w-full py-2 rounded-lg text-white font-medium bg-gradient-to-r from-blue-600 to-orange-500">
                            Pieslēgties
                        </button>
                    </form>
                    <p class="mt-4 text-center text-sm text-gray-600">
                        Nav konta?
                        <a href="{{ route('register') }}" class="text-blue-600 font-medium hover:underline">Reģistrēties</a>
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
