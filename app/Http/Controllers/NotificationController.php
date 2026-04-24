<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Rāda visus pazīņojumus un atzīmē nelasitos kā lasitus
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);
        $request->user()->unreadNotifications->markAsRead();

        return view('notifications.index', compact('notifications'));
    }

    // Atzīmē konkrētu pazīņojumu kā lasitu un novirza uz saiti
    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        $data = $notification->data;
        if (!empty($data['link'])) {
            return redirect($data['link']);
        }

        return back();
    }

    // Atzīmē visus pazīņojumus kā lasitus
    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Visas paziņojumi atzīmēti kā lasīti.');
    }
}
