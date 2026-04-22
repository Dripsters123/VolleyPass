<x-app-layout title="Par mums – VolleyPass">

    {{-- Hero --}}
    <section class="bg-gray-950 relative overflow-hidden">
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-blue-600/20 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-orange-500/20 blur-3xl pointer-events-none"></div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
            <span class="inline-block mb-4 px-3 py-1 rounded-full text-xs font-semibold tracking-widest uppercase bg-orange-500/15 text-orange-400 border border-orange-500/30">
                Par mums
            </span>
            <h1 class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight leading-tight">
                Volejbols – ne tikai sports,<br>
                <span class="bg-gradient-to-r from-orange-400 to-blue-500 bg-clip-text text-transparent">bet pieredze.</span>
            </h1>
            <p class="mt-6 text-lg text-gray-400 max-w-2xl mx-auto">
                VolleyPass ir radīts, lai padarītu volejbola biļešu iegādi, spēļu pārvaldību un fanu pieredzi pēc iespējas ērtāku un modernāku.
            </p>
        </div>
    </section>

    {{-- Mission & Values --}}
    <section class="bg-white py-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8">

                <div class="group p-8 rounded-2xl border border-gray-100 hover:border-orange-200 hover:shadow-lg transition-all">
                    <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                  d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Vienkārša biļešu iegāde</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Atrod spēli, izvēlies vietu un saņem e-biļeti sekundēs. Bez liekiem soļiem, bez aizķeršanās.
                    </p>
                </div>

                <div class="group p-8 rounded-2xl border border-gray-100 hover:border-blue-200 hover:shadow-lg transition-all">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                  d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v3M12 14v3M16 14v3"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Arēnu pārvaldība</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Organizatori var reģistrēt arēnas, pārvaldīt vietas un publicēt spēles tieši platformā.
                    </p>
                </div>

                <div class="group p-8 rounded-2xl border border-gray-100 hover:border-emerald-200 hover:shadow-lg transition-all">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Droša platforma</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Droši maksājumi, e-maciņš un datu aizsardzība – uzticamība ikvienā darījumā.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-gray-950 py-16">
        <div class="max-w-2xl mx-auto px-4 text-center">
            <h2 class="text-2xl font-extrabold text-white mb-4">Gatavs pirmajam mačam?</h2>
            <p class="text-gray-400 mb-8">Reģistrējies bez maksas un iegādājies biļetes pirmajās sekundēs.</p>
            <div class="flex flex-wrap gap-3 justify-center">
                <a href="{{ route('register') }}"
                   class="px-6 py-3 rounded-xl font-semibold bg-gradient-to-r from-orange-500 to-blue-600 text-white hover:opacity-90 transition-opacity">
                    Sākt tagad
                </a>
                <a href="{{ route('local.matches.index') }}"
                   class="px-6 py-3 rounded-xl font-semibold border border-white/20 text-gray-300 hover:text-white hover:border-white/40 transition-colors">
                    Skatīt spēles
                </a>
            </div>
        </div>
    </section>

</x-app-layout>
