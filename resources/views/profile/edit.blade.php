<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-3xl text-blue-700 leading-tight">
            Profila pārvaldība
        </h2>
        <p class="mt-1 text-gray-600">Atjauniniet savu informāciju šeit.</p>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="bg-white shadow-lg rounded-2xl p-8 hover:shadow-xl transition duration-300">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4">Profila informācija</h3>
                <p class="text-gray-500 mb-6">Atjauniniet savu vārdu, e-pastu un citas pamatinformācijas detaļas.</p>

                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="bg-white shadow-lg rounded-2xl p-8 hover:shadow-xl transition duration-300 border-t border-red-100">
                <h3 class="text-2xl font-semibold text-red-600 mb-4">Dzēst kontu</h3>
                <p class="text-gray-500 mb-6">
                    Šī darbība ir neatgriezeniska. Jūsu konts un visi dati tiks dzēsti.
                </p>

                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
