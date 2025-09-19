<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket; // make sure your Ticket model exists
use Illuminate\Support\Facades\Auth;

class TicketsController extends Controller
{
    public function index()
    {
        // Fetch tickets for the logged-in user
        $tickets = Ticket::where('user_id', Auth::id())->get();

        // Pass them to the view
        return view('tickets.index', compact('tickets'));
    }
}
