<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seat;
use App\Models\Ticket;
use App\Models\Event;
use App\Models\DiscountCard;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\StripeClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketPurchased;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    protected function stripeDebugLog($label, $data)
    {
        try {
            $path = storage_path('logs/stripe_debug.log');
            $entry = '['.now()->toDateTimeString().'] ' . $label . ' : ' . var_export($data, true) . PHP_EOL . PHP_EOL;
            File::append($path, $entry);
        } catch (\Throwable $e) {
            Log::warning('stripeDebugLog failed: ' . $e->getMessage());
        }
    }


    public function checkout(Request $request)
    {
        $request->validate([
            'match_id' => 'required|integer',
            'seats' => 'required|array|min:1',
            'seats.*.seat_id' => 'required|integer',
            'seats.*.price' => 'required|numeric|min:0',
            'discount_code' => 'nullable|string',
        ]);

        $matchId = (int) $request->input('match_id');
        $seats = $request->input('seats', []);
        $user = $request->user();
        $discountCode = $request->input('discount_code');

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        if (is_array($request->input('discount_code'))) {
            return response()->json(['error' => 'Tikai vienu atlaižu kodu var izmantot'], 400);
        }
        if (is_string($discountCode) && strpos($discountCode, ',') !== false) {
            return response()->json(['error' => 'Tikai vienu atlaižu kodu var izmantot'], 400);
        }

        $appliedDiscount = null;
        $newUserPromo = false;
        if (!empty($discountCode)) {
            $discountCode = trim($discountCode);
            $d = DiscountCard::where('code', $discountCode)->first();
            if (! $d || ! $d->active) {
                return response()->json(['error' => 'Invalid or inactive discount code'], 400);
            }
            if ($d->user_id && ((string)$d->user_id !== (string)$user->id)) {
                return response()->json(['error' => 'Discount code belongs to another user'], 403);
            }
            $appliedDiscount = $d;
        } else {
            // New-user promo: 20% off the first 3 ticket purchases
            $paidTicketCount = Ticket::where('user_id', $user->id)->where('status', 'paid')->count();
            if ($paidTicketCount < 3) {
                $newUserPromo = true;
            }
        }

        DB::beginTransaction();
        try {
            $reservationWindow = Carbon::now()->addMinutes(15);
            foreach ($seats as $s) {
                $seatId = $s['seat_id'] ?? null;
                if (! $seatId) {
                    DB::rollBack();
                    return response()->json(['error' => 'Nepareizs seat_id'], 400);
                }

                $seat = Seat::where('id', $seatId)
                    ->where('match_id', $matchId)
                    ->lockForUpdate()
                    ->first();

                if (! $seat) {
                    DB::rollBack();
                    return response()->json(['error' => "Sēdvietas {$seatId} nav atrasts"], 404);
                }

                if (!is_null($seat->ticket_id)) {
                    DB::rollBack();
                    return response()->json(['error' => "Sēdvieta {$seatId} ir jau nopirkta"], 409);
                }

                if ($seat->reserved_by !== null && $seat->reserved_by !== $user->id) {
                    if ($seat->reserved_until === null || $seat->reserved_until->isFuture()) {
                        DB::rollBack();
                        return response()->json(['error' => "Sēdvieta {$seatId} ir uz laiku rezervēta"], 409);
                    }
                }

                $seat->reserved_by = $user->id;
                $seat->reserved_until = $reservationWindow;
                $seat->save();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Checkout reservation error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to reserve seats'], 500);
        }

        $discountPercent = $appliedDiscount ? (int)$appliedDiscount->discount_percent : ($newUserPromo ? 20 : 0);
        $lineItems = [];
        $originalTotal = 0.0;
        foreach ($seats as $s) {
            $originalPrice = (float) $s['price'];
            $originalTotal += $originalPrice;
            $originalCents = (int) round($originalPrice * 100);
            $discountedCents = $originalCents;
            if ($discountPercent > 0) {
                $discountedCents = (int) round($originalCents * (1 - ($discountPercent / 100)));
                if ($discountedCents < 0) $discountedCents = 0;
            }

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => "Match #{$matchId} – Seat {$s['seat_id']}",
                        'metadata' => [
                            'original_amount_cents' => $originalCents,
                        ],
                    ],
                    'unit_amount' => $discountedCents,
                ],
                'quantity' => 1,
            ];
        }

        try {
            $sessionMetadata = [
                'match_id' => (string) $matchId,
                'seats' => json_encode($seats),
                'user_id' => (string) $user->id,
            ];
            if ($appliedDiscount) {
                $sessionMetadata['discount_code'] = $appliedDiscount->code;
                $sessionMetadata['discount_percent'] = (string) $appliedDiscount->discount_percent;
            }
            if ($newUserPromo) {
                $sessionMetadata['new_user_promo'] = '1';
                $sessionMetadata['discount_percent'] = '20';
            }

            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.cancel'),
                'metadata' => $sessionMetadata,
            ]);

            $this->stripeDebugLog('checkout.created_session', ['session_id' => $session->id, 'metadata' => $sessionMetadata]);

            return response()->json(['url' => $session->url]);
        } catch (\Throwable $e) {
            Log::error('Stripe session creation failed: ' . $e->getMessage());
            $this->stripeDebugLog('checkout.create_error', $e->getMessage());
            return response()->json(['error' => 'Payment provider error'], 500);
        }
    }

    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        $this->stripeDebugLog('success.called', ['session_id' => $sessionId, 'query' => $request->query()]);

        if (! $sessionId) {
            return redirect()->route('home')->with('error', 'No session ID provided.');
        }

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));
            $session = $stripe->checkout->sessions->retrieve($sessionId, ['expand' => ['payment_intent', 'customer']]);
        } catch (\Throwable $e) {
            $this->stripeDebugLog('success.retrieve_error', $e->getMessage());
            Log::error('Stripe session retrieve failed: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Could not verify payment session.');
        }

        $this->stripeDebugLog('success.session', $session);

        $metadata = $session->metadata ?? (object) [];
        $matchId = $metadata->match_id ?? null;
        $userId = $metadata->user_id ?? null;
        $seatsArray = json_decode($metadata->seats ?? '[]', true) ?: [];
        $paymentIntentId = is_object($session->payment_intent) ? $session->payment_intent->id : $session->payment_intent;

        if (!empty($metadata->discount_code)) {
            try {
                $dcode = (string) $metadata->discount_code;
                $drecord = DiscountCard::where('code', $dcode)->lockForUpdate()->first();
                if ($drecord && $drecord->active) {
                    $drecord->active = false;
                    $drecord->stripe_payment_intent = $paymentIntentId;
                    $drecord->used_at = now();
                    $drecord->save();
                    $this->stripeDebugLog('success.discount_consumed', $drecord->toArray());
                }
            } catch (\Throwable $ex) {
                $this->stripeDebugLog('success.discount_error', $ex->getMessage());
                Log::warning('Failed to mark discount used (success): ' . $ex->getMessage());
            }
        }

        try {
            $existing = Ticket::where('stripe_payment_intent', $paymentIntentId)->first();
            if ($existing) {
                $this->stripeDebugLog('success.ticket_exists', $existing->toArray());
                return redirect()->route('tickets.index')->with('success', 'Ticket purchased successfully!');
            }

            DB::beginTransaction();

            $seatIds = [];
            $totalAmount = 0.0;
            foreach ($seatsArray as $s) {
                $seatId = $s['seat_id'] ?? null;
                $price = isset($s['price']) ? (float)$s['price'] : 0.0;
                if ($seatId) $seatIds[] = $seatId;
                $totalAmount += $price;
            }

            // Always use Stripe's actual charged amount so discounts are reflected
            if (property_exists($session, 'amount_total') && $session->amount_total !== null) {
                $totalAmount = $session->amount_total / 100;
            }

            $ticket = new Ticket();
            $ticket->user_id = $userId;
            $ticket->event_id = $matchId;
            $ticket->ticket_type = 'seat';
            $ticket->quantity = max(1, count($seatIds));
            $ticket->amount_paid = $totalAmount;
            $ticket->currency = $session->currency ?? 'eur';
            $ticket->status = 'paid';
            $ticket->stripe_payment_intent = $paymentIntentId;
            $ticket->stripe_email = $session->customer_details->email ?? $session->customer_email ?? null;
            $ticket->claim_token = Str::random(48);
            $ticket->save();

            $this->stripeDebugLog('success.ticket_created', $ticket->toArray());

            foreach ($seatIds as $seatId) {
                $seat = Seat::where('id', $seatId)
                    ->where('match_id', $matchId)
                    ->lockForUpdate()
                    ->first();

                if (! $seat) {
                    $this->stripeDebugLog('success.seat_not_found', $seatId);
                    continue;
                }

                if ($seat->ticket_id === null) {
                    $seat->ticket_id = $ticket->id;
                }
                $seat->reserved_by = null;
                $seat->reserved_until = null;
                $seat->user_id = $userId;
                $seat->save();

                $this->stripeDebugLog('success.seat_attached', ['seat_id' => $seatId, 'ticket_id' => $ticket->id]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->stripeDebugLog('success.finalize_error', $e->getMessage());
            Log::error('Finalize purchase (success) failed: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Could not finalize purchase.');
        }

        try {
            $emailAddress = $ticket->stripe_email ?? (\App\Models\User::find($userId)?->email);
            $matchModel = \App\Models\VolleyballMatch::find($matchId);
            if ($emailAddress && $matchModel) {
                Mail::to($emailAddress)->queue(new TicketPurchased($ticket, $matchModel, $seatsArray));
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to queue TicketPurchased email: ' . $e->getMessage());
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket purchased successfully!');
    }

    public function cancel()
    {
        return view('payment.cancel');
    }


    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        $this->stripeDebugLog('webhook.raw_payload', $payload);
        $this->stripeDebugLog('webhook.headers', $request->headers->all());

        try {
            if ($endpointSecret) {
                $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
            } else {
                $eventArray = json_decode($payload, true);
                $event = \Stripe\Event::constructFrom($eventArray);
            }
        } catch (\UnexpectedValueException $e) {
            $this->stripeDebugLog('webhook.invalid_payload', $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            $this->stripeDebugLog('webhook.invalid_signature', $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $this->stripeDebugLog('webhook.event', $event);

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $metadata = $session->metadata ?? (object) [];
            $this->stripeDebugLog('webhook.session', $session);

            $seatsJson = $metadata->seats ?? null;
            $seatsArray = [];
            if ($seatsJson) {
                $seatsArray = json_decode($seatsJson, true) ?: [];
            }

            $matchId = $metadata->match_id ?? null;
            $userId = $metadata->user_id ?? null;
            $paymentIntent = is_object($session->payment_intent) ? $session->payment_intent->id : $session->payment_intent;
            $amountPaid = property_exists($session, 'amount_total') ? ($session->amount_total / 100) : null;

            if (!empty($metadata->discount_code)) {
                try {
                    $dcode = (string)$metadata->discount_code;
                    $drecord = DiscountCard::where('code', $dcode)->lockForUpdate()->first();
                    if ($drecord && $drecord->active) {
                        $drecord->active = false;
                        $drecord->stripe_payment_intent = $paymentIntent;
                        $drecord->used_at = now();
                        $drecord->save();
                        $this->stripeDebugLog('webhook.discount_consumed', $drecord->toArray());
                    }
                } catch (\Throwable $ex) {
                    $this->stripeDebugLog('webhook.discount_error', $ex->getMessage());
                }
            }

            $existingTicket = Ticket::where('stripe_payment_intent', $paymentIntent)->first();
            if ($existingTicket) {
                $this->stripeDebugLog('webhook.ticket_exists', $existingTicket->toArray());
                return response()->json(['status' => 'already_handled'], 200);
            }

            DB::beginTransaction();
            try {
                $seatIds = [];
                $totalAmount = 0.0;
                foreach ($seatsArray as $s) {
                    $seatId = $s['seat_id'] ?? null;
                    $price = isset($s['price']) ? (float)$s['price'] : null;
                    if ($seatId) {
                        $seatIds[] = $seatId;
                        $totalAmount += ($price ?? 0);
                    }
                }

                if (empty($seatIds) && $amountPaid !== null) {
                   
                    $ticket = new Ticket();
                    $ticket->user_id = $userId ?: null;
                    $ticket->event_id = $matchId ?: null;
                    $ticket->ticket_type = 'seat';
                    $ticket->quantity = 1;
                    $ticket->amount_paid = $amountPaid;
                    $ticket->currency = $session->currency ?? 'eur';
                    $ticket->status = 'paid';
                    $ticket->stripe_email = $session->customer_details->email ?? $session->customer_email ?? null;
                    $ticket->stripe_payment_intent = $paymentIntent;
                    $ticket->claim_token = Str::random(48);
                    $ticket->save();
                    $this->stripeDebugLog('webhook.ticket_created_no_seats', $ticket->toArray());
                } elseif (!empty($seatIds)) {
                    $ticket = new Ticket();
                    $ticket->user_id = $userId ?: null;
                    $ticket->event_id = $matchId ?: null;
                    $ticket->ticket_type = 'seat';
                    $ticket->quantity = count($seatIds);
                    $ticket->amount_paid = $amountPaid ?? ($totalAmount ?: 0.0);
                    $ticket->currency = $session->currency ?? 'eur';
                    $ticket->status = 'paid';
                    $ticket->stripe_email = $session->customer_details->email ?? $session->customer_email ?? null;
                    $ticket->stripe_payment_intent = $paymentIntent;
                    $ticket->claim_token = Str::random(48);
                    $ticket->save();
                    $this->stripeDebugLog('webhook.ticket_created', $ticket->toArray());

                    foreach ($seatIds as $seatId) {
                        $dbSeat = Seat::where('id', $seatId)->where('match_id', $matchId)->lockForUpdate()->first();
                        if ($dbSeat) {
                            if ($dbSeat->ticket_id === null) {
                                $dbSeat->ticket_id = $ticket->id;
                            }
                            $dbSeat->user_id = $userId ?: $dbSeat->user_id;
                            $dbSeat->reserved_by = null;
                            $dbSeat->reserved_until = null;
                            $dbSeat->save();
                            $this->stripeDebugLog('webhook.seat_attached', ['seat_id' => $seatId, 'ticket_id' => $ticket->id]);
                        } else {
                            $this->stripeDebugLog('webhook.seat_missing', $seatId);
                        }
                    }
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->stripeDebugLog('webhook.finalize_error', $e->getMessage());
                Log::warning('Webhook processing failed: ' . $e->getMessage());
                return response()->json(['error' => 'Webhook processing failed'], 500);
            }

            return response()->json(['status' => 'success'], 200);
        }

        return response()->json(['status' => 'ignored'], 200);
    }

    public function validateDiscount(Request $request)
    {
        $request->validate([
            'discount_code' => 'required|string',
            'seats' => 'nullable|array',
            'seats.*.price' => 'nullable|numeric',
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $code = trim($request->input('discount_code'));
        $d = DiscountCard::where('code', $code)->first();

        if (! $d || ! $d->active) {
            return response()->json(['error' => 'Invalid or inactive discount code'], 400);
        }

        if ($d->user_id && ((string) $d->user_id !== (string) $user->id)) {
            return response()->json(['error' => 'Discount code belongs to another user'], 403);
        }

        $percent = (int) $d->discount_percent;

        $response = [
            'discount_percent' => $percent,
        ];

        $seats = $request->input('seats', null);
        if (is_array($seats) && count($seats) > 0) {
            $originalTotal = 0.0;
            foreach ($seats as $s) {
                $price = isset($s['price']) ? (float)$s['price'] : 0.0;
                $originalTotal += $price;
            }
            $discountedTotal = $originalTotal * (1 - ($percent / 100));
            if ($discountedTotal < 0) $discountedTotal = 0.0;

            $response['original_total'] = round($originalTotal, 2);
            $response['discounted_total'] = round($discountedTotal, 2);
            $response['saving'] = round($originalTotal - $discountedTotal, 2);
        }

        return response()->json($response, 200);
    }
}
