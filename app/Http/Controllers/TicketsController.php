<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class TicketsController extends Controller
{

    public function index(Request $request)
    {
        $query = Ticket::with(['event', 'seats'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('event')) {
            $query->whereHas('event', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->event . '%');
            });
        }

        $tickets = $query->paginate(10)->withQueryString();

        return view('tickets.index', compact('tickets'));
    }
}
