<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmed;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class ProductController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function index(Request $request)
    {
        $query = Product::query()->where('status', 'active');

        if ($request->filled('mine') && auth()->check()) {
            $query->where('user_id', auth()->id());
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->max_price);
        }

        match($request->input('sort')) {
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            default      => $query->orderBy('created_at', 'desc'),
        };

        $products = $query->paginate(12)->withQueryString();
        return view('products.index', compact('products'));
    }

    public function show(Product $product)
    {
        if ($product->status !== 'active') abort(404);
        return view('products.show', compact('product'));
    }
    public function create()
{
    return view('products.create');
}

public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'image' => 'nullable|image|max:2048',
    ]);

    $path = $request->file('image')?->store('products', 'public');

    $product = Product::create([
        'user_id' => auth()->id(),
        'title' => $request->title,
        'description' => $request->description,
        'price' => $request->price,
        'currency' => 'eur',
        'image_path' => $path,
        'status' => 'active',
    ]);

    return redirect()->route('products.show', $product)
        ->with('success', 'Product created successfully!');
}
    // Funkcija, kas apstrādā produkta pirkšanas procesu, izmantojot Stripe
public function buy(Request $request, Product $product)
{
    // Neļauj lietotājam nopirkt savu produktu
    if ($product->user_id == $request->user()->id) {
        return redirect()->route('products.show', $product)
            ->with('error', 'Jūs nevarat iegādāties savu produktu.');
    }

    // Pārbauda, vai produkts ir aktīvs un pieejams pirkšanai
    if ($product->status !== 'active') {
        return redirect()->route('products.show', $product)
            ->with('error', 'Produkts nav pieejams.');
    }

    // Ja lietotājs nav pieslēdzies – pāradresē uz pieteikšanās lapu
    $user = $request->user();
    if (! $user) return redirect()->route('login');

    // Izveido jaunu pasūtījumu datubāzē ar statusu "pending"
    $order = Order::create([
        'buyer_id' => $user->id,
        'product_id' => $product->id,
        'amount' => $product->price,
        'currency' => $product->currency ?? 'eur',
        'status' => 'pending',
    ]);

    try {
        // Izveido Stripe Checkout sesiju
        $session = StripeSession::create([
            'payment_method_types' => ['card'], // Atļautie maksājuma veidi
            'line_items' => [[
                'price_data' => [
                    'currency' => $product->currency ?? 'eur',
                    'product_data' => ['name' => $product->title], // Produkta nosaukums Stripe sesijā
                    'unit_amount' => (int) round($product->price * 100), // Cena centos
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            // Norāda URL, uz kuru lietotājs tiks novirzīts pēc veiksmīga maksājuma
            'success_url' => route('products.purchase_success', ['order' => $order->id]) . '&session_id={CHECKOUT_SESSION_ID}',
            // Norāda URL, ja lietotājs atceļ maksājumu
            'cancel_url' => route('products.purchase_cancel', ['order' => $order->id]),
            'metadata' => [
                'order_id' => (string) $order->id, // Pievieno pasūtījuma ID metadatos
            ],
        ]);
    } catch (\Throwable $e) {
        // Ja Stripe radās kļūda – pieraksta to log failā un paziņo lietotājam
        \Log::error("Stripe checkout session error: {$e->getMessage()}");
        return redirect()->route('products.show', $product)
            ->with('error', 'Maksājumu sistēmas kļūda.');
    }

    // Pāradresē lietotāju uz Stripe maksājuma lapu
    return redirect()->away($session->url);
}


// Funkcija, kas apstrādā veiksmīgu pirkumu pēc Stripe maksājuma
public function purchaseSuccess(Request $request)
{
    $order = Order::find($request->order);

    // Ja pasūtījums vēl nav apmaksāts, atjauno tā statusu uz "paid"
    if ($order && $order->status === 'pending') {
        $order->status = 'paid';
        $order->save();

        try {
            $order->load('product', 'buyer');
            if ($order->buyer?->email) {
                Mail::to($order->buyer->email)->send(new OrderConfirmed($order));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send OrderConfirmed email: ' . $e->getMessage());
        }
    }

    // Pāradresē lietotāju ar veiksmīga maksājuma ziņu
    return redirect()->route('products.index')
        ->with('success', 'Maksājums pabeigts — pasūtījums tiek apstrādāts.');
}


// Funkcija, kas apstrādā atceltu pirkumu (ja lietotājs pārtrauc maksājumu Stripe)
public function purchaseCancel(Request $request)
{
    $order = Order::find($request->order);

    // Ja pasūtījums bija gaidīšanas režīmā, atceļ to
    if ($order && $order->status === 'pending') {
        $order->status = 'cancelled';
        $order->save();
    }

    // Pāradresē atpakaļ uz produkta lapu ar kļūdas ziņu
    return redirect()->route('products.show', $order->product)
        ->with('error', 'Pasūtījums tika atcelts.');
}

}
