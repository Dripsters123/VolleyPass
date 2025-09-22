<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seat;
use App\Models\Ticket;
use App\Models\Event;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\StripeClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    
    public function checkout(Request $request)
    {
    // Validācija ienākošajiem datiem no servera puses
        $request->validate([
            'match_id' => 'required|string',
            'seat_id' => 'nullable|string',
            'seat_number' => 'nullable|string',
            'seat_row' => 'nullable|string',
            'price' => 'required|numeric',
    ]);

        $matchId = $request->input('match_id');
        $seatId = $request->input('seat_id'); 
        $seatNumber = $request->input('seat_number');
        $seatRow = $request->input('seat_row');
        $price = (float) $request->input('price');

        
        $reservedSeat = null;
        try {
            // Attempt to find a matching Seat record. Best-effort: match by match_id + seat_number
            $seatQuery = Seat::where('match_id', $matchId);
            if ($seatNumber !== null) {
                $seatQuery->where('seat_number', $seatNumber);
            } elseif ($seatId !== null) {
                $seatQuery->where('seat_number', $seatId);
            } else {
                // no reliable seat identifier supplied — skip reservation
                $seatQuery = null;
            }

            if ($seatQuery) {
                $reservedSeat = $seatQuery->lockForUpdate()->first();
                if ($reservedSeat && $reservedSeat->is_taken) {
                    return response()->json(['error' => 'Seat already taken'], 409);
                }
                if ($reservedSeat) {
                    $reservedSeat->update([
                        'is_taken' => true,
                        'user_id' => auth()->id(),
                        'is_fake' => false,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Seat reservation attempt failed: ' . $e->getMessage());
            // continue — we will still create a session
        }

        
        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Match #' . $matchId . ' - Seat ' . ($seatNumber ?? $seatId ?? 'general'),
                    ],
                    'unit_amount' => (int) round($price * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.cancel'),
            'metadata' => [
                'match_id' => (string) $matchId,
                'seat_id' => (string) ($seatId ?? ''),
                'seat_number' => (string) ($seatNumber ?? ''),
                'seat_row' => (string) ($seatRow ?? ''),
                'user_id' => (string) auth()->id(),
                'price' => (string) number_format($price, 2, '.', ''),
            ],
        ]);

        return response()->json(['url' => $session->url]);
    }

    // success redirect (optional double-check, reads session)
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return redirect()->route('home')->with('error', 'No session ID provided.');
        }

        $stripe = new StripeClient(config('services.stripe.secret'));
        $session = $stripe->checkout->sessions->retrieve($sessionId, [
            'expand' => ['payment_intent', 'customer'],
        ]);

        // create/find Event
        $matchId = $session->metadata->match_id ?? null;
        $event = null;
        if ($matchId) {
            $event = Event::firstOrCreate(
                ['id' => $matchId],
                [
                    'name' => 'External Match #' . $matchId,
                    'start_time' => Carbon::now()->timezone('Europe/Riga'),
                    'description' => 'Imported from external API',
                ]
            );
        }

        // Create ticket record if not already created by webhook (webhook is primary)
        // (We check by payment_intent id)
        $paymentIntentId = is_object($session->payment_intent) ? $session->payment_intent->id : $session->payment_intent;

        $existing = Ticket::where('stripe_payment_intent', $paymentIntentId)->first();
        if (!$existing) {
            Ticket::create([
                'user_id' => $session->metadata->user_id ?? auth()->id(),
                'event_id' => $event?->id,
                'ticket_type' => 'seat',
                'quantity' => 1,
                'amount_paid' => (float) ($session->metadata->price ?? ($session->amount_total / 100)),
                'currency' => $session->currency ?? 'eur',
                'seat_number' => $session->metadata->seat_number ?? null,
                'status' => 'paid',
                'stripe_email' => $session->customer_details->email ?? $session->customer_email ?? null,
                'stripe_payment_intent' => $paymentIntentId,
            ]);
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket purchased successfully!');
    }

    public function cancel()
    {
        return view('payment.cancel');
    }

    // Stripe webhook — verify signature using the webhook secret (recommended)
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            if ($endpointSecret) {
                $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
            } else {
                // Fallback (not recommended): parse raw body if no secret configured
                $eventArray = json_decode($payload, true);
                $event = \Stripe\Event::constructFrom($eventArray);
            }
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $metadata = $session->metadata ?? (object)[];

            $seatId = $metadata->seat_id ?? null;
            $seatNumber = $metadata->seat_number ?? null;
            $matchId = $metadata->match_id ?? null;
            $userId = $metadata->user_id ?? null;

            // Mark seat as taken if found (best-effort)
            try {
                $seatQuery = Seat::where('match_id', $matchId);
                if (!empty($seatNumber)) {
                    $seatQuery->where('seat_number', $seatNumber);
                } elseif (!empty($seatId)) {
                    $seatQuery->where('seat_number', $seatId);
                }
                $dbSeat = $seatQuery->first();
                if ($dbSeat) {
                    $dbSeat->update([
                        'is_taken' => true,
                        'user_id' => $userId ?: $dbSeat->user_id,
                        'is_fake' => false,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Webhook seat update failed: ' . $e->getMessage());
            }

            // Create ticket (idempotent by payment_intent)
            $paymentIntent = $session->payment_intent;
            $amountPaid = property_exists($session, 'amount_total') ? ($session->amount_total / 100) : ($metadata->price ?? null);

            $existing = Ticket::where('stripe_payment_intent', $paymentIntent)->first();
            if (!$existing) {
                Ticket::create([
                    'user_id' => $userId ?: null,
                    'event_id' => $matchId ?: null,
                    'ticket_type' => 'seat',
                    'quantity' => 1,
                    'amount_paid' => (float) $amountPaid,
                    'currency' => $session->currency ?? 'eur',
                    'seat_number' => $seatNumber ?? $seatId ?? null,
                    'status' => 'paid',
                    'stripe_email' => $session->customer_details->email ?? $session->customer_email ?? null,
                    'stripe_payment_intent' => $paymentIntent,
                ]);
            }

            return response()->json(['status' => 'success'], 200);
        }

        return response()->json(['status' => 'unhandled event'], 200);
    }
}
