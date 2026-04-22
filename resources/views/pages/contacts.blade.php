<x-app-layout title="Kontakti – VolleyPass">

    {{-- Page header --}}
    <section class="bg-gray-950 relative overflow-hidden">
        <div class="absolute -top-20 -right-20 w-80 h-80 rounded-full bg-blue-600/15 blur-3xl pointer-events-none"></div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
            <span class="inline-block mb-4 px-3 py-1 rounded-full text-xs font-semibold tracking-widest uppercase bg-blue-500/15 text-blue-400 border border-blue-500/30">
                Kontakti
            </span>
            <h1 class="text-4xl font-extrabold text-white tracking-tight">Sazinies ar mums</h1>
            <p class="mt-4 text-gray-400 max-w-xl mx-auto">Jautājumi par platformu, biļetēm vai sadarbību? Esam gatavi palīdzēt.</p>
        </div>
    </section>

    {{-- Contact cards --}}
    <section class="bg-gray-50 py-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-8 items-start">

                {{-- Contact info --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="h-1.5 w-full bg-gradient-to-r from-orange-400 to-blue-500"></div>
                    <div class="p-8 space-y-6">
                        <h2 class="text-xl font-bold text-gray-900">Kontaktinformācija</h2>

                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                          d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-0.5">Telefons</p>
                                <p class="text-gray-800 font-medium">+371 25434994</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-0.5">E-pasts</p>
                                <p class="text-gray-800 font-medium break-all">ipb22.k.skrastins@vtdt.edu.lv</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                          d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-0.5">Adrese</p>
                                <p class="text-gray-800 font-medium">Cēsis, Latvija</p>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-gray-100">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-3">Sociālie tīkli</p>
                            <div class="flex gap-3">
                                <a href="#"
                                   class="w-9 h-9 rounded-lg bg-gray-100 hover:bg-blue-600 hover:text-white flex items-center justify-center text-gray-500 transition-colors text-sm font-semibold">
                                    f
                                </a>
                                <a href="#"
                                   class="w-9 h-9 rounded-lg bg-gray-100 hover:bg-pink-500 hover:text-white flex items-center justify-center text-gray-500 transition-colors">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                    </svg>
                                </a>
                                <a href="#"
                                   class="w-9 h-9 rounded-lg bg-gray-100 hover:bg-gray-900 hover:text-white flex items-center justify-center text-gray-500 transition-colors text-sm font-bold">
                                    X
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Developer card --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="h-1.5 w-full bg-gradient-to-r from-blue-500 to-orange-400"></div>
                    <div class="p-8">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-4">Izstrādātājs</p>
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-orange-400 to-blue-600 flex items-center justify-center text-white text-xl font-extrabold shrink-0">
                                K
                            </div>
                            <div>
                                <p class="font-bold text-gray-900 text-lg">Kristers Skrastiņš</p>
                                <p class="text-sm text-gray-500">Full-stack izstrādātājs</p>
                            </div>
                        </div>
                        <div class="rounded-xl overflow-hidden border border-gray-100">
                            <img src="{{ asset('images/selfie.jpg') }}"
                                 alt="Kristers Skrastiņš"
                                 class="w-full h-56 object-cover object-top">
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

</x-app-layout>
