<x-app-layout>
    <div class="max-w-lg mx-auto px-4 py-16 text-center">
        <div class="mb-6 flex items-center justify-center w-16 h-16 mx-auto rounded-full bg-yellow-100">
            <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-3">Pirkums atcelts</h1>
        <p class="text-gray-600 mb-2">Tavs pirkums tika atcelts vai sesija beidzās.</p>
        <p class="text-gray-500 text-sm mb-8">Sēdvietu rezervācija tika atbrīvota. Vari mēģināt vēlreiz.</p>
        <a href="{{ route('local.matches.index') }}"
           class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold bg-gradient-to-r from-orange-500 to-blue-600 text-white hover:opacity-90 transition-opacity shadow-lg">
            Atpakaļ uz spēlēm
        </a>
    </div>
</x-app-layout>
