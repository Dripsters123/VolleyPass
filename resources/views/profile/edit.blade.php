<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-3xl text-blue-700 leading-tight">
            Profila pārvaldība
        </h2>
        <p class="mt-1 text-gray-600 dark:text-gray-400">Atjauniniet savu informāciju šeit.</p>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Avatar section --}}
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-2xl p-8">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-6">Profila attēls</h3>
                <div class="flex items-center gap-6">
                    <div class="w-20 h-20 rounded-2xl overflow-hidden bg-gradient-to-br from-orange-400 to-blue-600 flex items-center justify-center flex-shrink-0">
                        @if($user->avatar)
                            <img src="{{ Storage::url($user->avatar) }}" alt="avatar" class="w-full h-full object-cover">
                        @else
                            <span class="text-white text-3xl font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div>
                        <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="flex items-center gap-3">
                                <label class="cursor-pointer px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                                    Izvēlēties attēlu
                                    <input type="file" name="avatar" accept="image/*" class="hidden" onchange="this.form.submit()">
                                </label>
                                @if($user->avatar)
                                    <span class="text-xs text-green-600">✓ Attēls augšupielādēts</span>
                                @endif
                            </div>
                            @error('avatar') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </form>
                        @if(session('status') === 'avatar-updated')
                            <p class="mt-2 text-sm text-green-600">Profila attēls atjaunināts!</p>
                        @endif
                        <p class="mt-2 text-xs text-gray-400">JPG, PNG, GIF vai WebP. Maks. 2 MB.</p>
                    </div>
                </div>
            </div>

            {{-- Profile info --}}
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-2xl p-8 hover:shadow-xl transition duration-300">
                <h3 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">Profila informācija</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">Atjauniniet savu vārdu, e-pastu un citas pamatinformācijas detaļas.</p>

                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- Password --}}
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-2xl p-8 hover:shadow-xl transition duration-300">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-1">Mainīt paroli</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Izmantojiet garu, nejaušu paroli drošībai.</p>

                <div class="max-w-xl space-y-5">
                    @include('profile.partials.update-password-form')

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                            Vai nevarat ierakstīt pašreizējo paroli? Saņemiet atiestatīšanas saiti e-pastā.
                        </p>
                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf
                            <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2 border border-blue-200 dark:border-blue-700 text-blue-600 dark:text-blue-400 text-sm font-medium rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                                ✉️ Nosūtīt paroles maiņas saiti uz e-pastu
                            </button>
                            @if(session('status') === 'passwords.sent' || session('status') === 'We have emailed your password reset link.')
                                <p class="mt-2 text-sm text-green-600">Saite nosūtīta uz {{ auth()->user()->email }}</p>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            {{-- Delete account --}}
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
