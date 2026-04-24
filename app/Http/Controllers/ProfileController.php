<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use App\Models\VolleyballMatch;
use App\Models\MatchRequest;
use App\Models\Ticket;

class ProfileController extends Controller
{
    // Rāda profila rediģēšanas lapu ar statistiku (izveidoti mači, pārdotas biļetes u.c.)
    public function edit(Request $request): View
    {
        $user = $request->user();

        $matchesCreated = VolleyballMatch::where('created_by', $user->id)->count();

        $myMatchIds = VolleyballMatch::where('created_by', $user->id)->pluck('id');
        $ticketsSold = Ticket::whereIn('event_id', $myMatchIds)->count();

        $totalRevenue = Ticket::whereIn('event_id', $myMatchIds)->sum('amount_paid');

        $matchRequestsSubmitted = MatchRequest::where('user_id', $user->id)->count();

        $ticketsBought = Ticket::where('user_id', $user->id)->where('status', 'paid')->count();

        $pendingScoreMatches = VolleyballMatch::where('created_by', $user->id)
            ->where('end_time', '<', now())
            ->whereNotIn('match_state', ['completed'])
            ->whereNotIn('status_type', ['completed'])
            ->count();

        return view('profile.edit', compact(
            'user',
            'matchesCreated',
            'ticketsSold',
            'totalRevenue',
            'matchRequestsSubmitted',
            'ticketsBought',
            'pendingScoreMatches'
        ));
    }

    // Atjaunina profila datus (validē caur ProfileUpdateRequest)
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    // Dzēš lietotāja kontu pēc paroles apstiprinājuma
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ], [
            'avatar.required' => 'Lūdzu izvēlieties attēlu.',
            'avatar.image'    => 'Failam jābūt attēlam.',
            'avatar.mimes'    => 'Atļautie formāti: jpg, png, gif, webp.',
            'avatar.max'      => 'Attēla izmērs nedrīkst pārsniegt 2 MB.',
        ]);

        $user = $request->user();

        // Delete old avatar if it exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return Redirect::route('profile.edit')->with('status', 'avatar-updated');
    }
}
