<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Event;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\StripeClient;
use Carbon\Carbon;

class PaymentController extends Controller
{
    // Validācija ienākošajiem datiem no servera puses
   public function checkout(Request $request)
{
    $seat = Seat::lockForUpdate()->findOrFail($request->seat_id);

    if ($seat->is_taken) {
        return back()->withErrors('Seat already taken');
    }

    // Create Stripe session
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'eur',
                'product_data' => ['name' => "Seat {$seat->seat_number}"],
                'unit_amount' => 1000, // €10
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => route('payment.success'),
        'cancel_url' => route('payment.cancel'),
    ]);

    // Optionally mark seat as "reserved" temporarily
    $seat->update(['is_taken' => true, 'user_id' => auth()->id()]);

    return redirect($session->url);
}


    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return redirect()->route('home')->with('error', 'No session ID provided.');
        }

        $stripe = new StripeClient(config('services.stripe.secret'));
        $session = $stripe->checkout->sessions->retrieve($sessionId, [
            'expand' => ['line_items', 'payment_intent', 'customer'],
        ]);

        $metadata = $session->metadata;
        $matchId = $metadata->match_id;

        // Ensure Event exists
        $event = Event::firstOrCreate(
            ['id' => $matchId],
            [
                'name' => 'External Match #' . $matchId,
                'start_time' => Carbon::now()->timezone('Europe/Riga'),
                'description' => 'Imported from external API',
            ]
        );

        Ticket::create([
            'user_id' => $metadata->user_id ?? auth()->id(),
            'event_id' => $event->id,
            'ticket_type' => $metadata->ticket_type,
            'quantity' => $metadata->quantity,
            'amount_paid' => $metadata->price,
            'currency' => $session->currency,
            'seat_number' => $metadata->seat_number,
            'seat_row' => $metadata->seat_row,
            'seat_col' => $metadata->seat_col,
            'status' => 'paid',
            'stripe_email' => $session->customer_email,
            'stripe_payment_intent' => is_object($session->payment_intent)
                ? $session->payment_intent->id
                : $session->payment_intent,
        ]);

        return redirect()->route('tickets.index')->with('success', 'Ticket purchased successfully!');
    }

    public function cancel()
    {
        return view('payment.cancel');
    }

   public function webhook(Request $request)
{
    $event = \Stripe\Event::constructFrom($request->all());

    if ($event->type === 'checkout.session.completed') {
        $session = $event->data->object;

        // Find seat by metadata
        $seatId = $session->metadata->seat_id;
        $seat = Seat::find($seatId);

        if ($seat) {
            $seat->update([
                'is_taken' => true,
                'user_id' => $session->metadata->user_id,
                'is_fake' => false
            ]);

            Ticket::create([
                'user_id' => $seat->user_id,
                'event_id' => $seat->match_id,
                'ticket_type' => 'seat',
                'quantity' => 1,
                'amount_paid' => $session->amount_total / 100,
                'currency' => $session->currency,
                'seat_number' => $seat->seat_number,
                'status' => 'paid',
                'stripe_payment_intent' => $session->payment_intent,
            ]);
        }

        return response()->json(['status' => 'success']);
    }
}
}