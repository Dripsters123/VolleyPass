<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * D
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * 
     *
     * @throws \Illuminate\Validation\ValidationException
     */

  public function store(Request $request): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            // Password: at least 12 characters, mixed case, numbers and symbols
            'password' => [
                'required',
                'confirmed',
                Rules\Password::min(12)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ];

        $messages = [
            'email.unique' => 'Šāds e-pasts jau eksistē. Ja tas ir Jūsu e-pasts — izmēģiniet pieslēgties vai atiestatīt paroli.',
            'password.confirmed' => 'Paroles apstiprinājums neatbilst.',
        ];

        $validated = $request->validate($rules, $messages);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));
        Auth::login($user);

        // After registration redirect to dashboard (authenticated "sākums")
        return redirect()->route('dashboard');
    }
}
