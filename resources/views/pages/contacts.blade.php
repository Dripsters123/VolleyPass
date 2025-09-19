<x-app-layout title="Kontakti – Kristers Skrastiņš">
    <div class="max-w-5xl mx-auto px-6 py-12">
        <div class="grid md:grid-cols-2 gap-8 items-stretch">

            <!-- LEFT: Contact Info -->
            <div class="bg-white rounded-2xl shadow-lg flex items-center justify-center h-[500px]">
                <div class="w-full max-w-sm p-6 space-y-6">
                    <h2 class="text-2xl font-bold text-blue-700 mb-6">Kontakti</h2>

                    <!-- Phone -->
                    <div class="flex items-center gap-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 5h2l1 7h13l1-7h2m-3 7v6a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-6"/>
                        </svg>
                        <div>
                            <h3 class="font-semibold text-gray-700">Telefons</h3>
                            <p class="text-gray-600">25434994</p>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="flex items-center gap-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 12h2m-2 4h2m-6-8h6v6H8V8h6z"/>
                        </svg>
                        <div>
                            <h3 class="font-semibold text-gray-700">E-pasts</h3>
                            <p class="text-gray-600">ipb22.k.skrastins@vtdt.edu.lv</p>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="flex items-center gap-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 
                                     1.79-4 4 1.79 4 4 4zM6 20v-2a4 4 0 0 1 4-4h4a4 
                                     4 0 0 1 4 4v2"/>
                        </svg>
                        <div>
                            <h3 class="font-semibold text-gray-700">Adrese</h3>
                            <p class="text-gray-600">Cēsis, Latvija</p>
                        </div>
                    </div>

                    <!-- Socials -->
                    <div class="pt-4 flex justify-center gap-6">
                        <a href="#" class="text-blue-600 hover:text-blue-700">Facebook</a>
                        <a href="#" class="text-blue-600 hover:text-blue-700">Instagram</a>
                        <a href="#" class="text-blue-600 hover:text-blue-700">X</a>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Personal Card -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden h-[500px]">
                <div class="bg-gradient-to-r from-orange-400 to-blue-600 text-white px-6 py-4">
                    <h2 class="text-xl font-bold">Kristers Skrastiņš</h2>
                </div>
                <div class="h-[440px] flex items-center justify-center p-6">
                    <img src="{{ asset('images/selfie.jpg') }}" 
                         alt="Kristers Skrastiņš" 
                         class="w-full h-full object-cover rounded-xl shadow-md">
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
