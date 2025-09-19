<x-app-layout title="VolleyPass – Reģistrēties">
    <div class="max-w-5xl mx-auto px-6 py-12">
        <div class="grid md:grid-cols-2 gap-8 items-stretch">

            <!-- LEFT: Slideshow -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden h-[500px]">
                <div class="bg-gradient-to-r from-orange-400 to-blue-600 text-white px-6 py-4">
                    <h2 class="text-xl font-bold">VolleyPass</h2>
                    <p class="text-sm">Reģistrējies, lai iegādātos biļetes</p>
                </div>
                <div x-data="{
                        active: 0,
                        images: ['/images/slide4.jpg','/images/slide5.jpg','/images/slide6.jpg'],
                        next(){ this.active = (this.active + 1) % this.images.length }
                    }"
                     x-init="setInterval(()=>next(), 4000)"
                     class="relative h-[440px]">
                    <template x-for="(image, idx) in images" :key="idx">
                        <div x-show="active===idx"
                             x-transition
                             class="absolute inset-0">
                            <img :src="image" alt=""
                                 class="w-full h-full object-cover">
                        </div>
                    </template>
                </div>
            </div>

            <!-- RIGHT: Register Form -->
            <div class="bg-white rounded-2xl shadow-lg flex items-center justify-center h-[500px]">
                <div class="w-full max-w-sm p-6">
                    <h2 class="text-2xl font-bold text-center mb-6">Reģistrēties</h2>
                    <form method="POST" action="{{ route('register') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium mb-1">Vārds</label>
                            <input type="text" name="name" required
                                   class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">E-pasts</label>
                            <input type="email" name="email" required
                                   class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Parole</label>
                            <input type="password" name="password" required
                                   class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Apstiprināt paroli</label>
                            <input type="password" name="password_confirmation" required
                                   class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <button type="submit"
                                class="w-full py-2 rounded-lg text-white font-medium bg-gradient-to-r from-blue-600 to-orange-500">
                            Reģistrēties
                        </button>
                    </form>
                    <p class="mt-4 text-center text-sm text-gray-600">
                        Jau ir konts?
                        <a href="{{ route('login') }}" class="text-blue-600 font-medium hover:underline">Pieslēgties</a>
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
