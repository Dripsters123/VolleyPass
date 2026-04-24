<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
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

    // Produktu saraksts ar meklēšanu, kategorijām un cenu filtriem
    public function index(Request $request)
    {
        $query = Product::query()->where('status', 'active');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
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

        $categories = config('products.categories');
        $products = $query->paginate(12)->withQueryString();
        return view('products.index', compact('products', 'categories'));
    }

    // Rāda produkta detaļlapu ar atsauksmēm un vērtējumiem
    public function show(Product $product)
    {
        if ($product->status !== 'active') abort(404);

        $product->load(['reviews.user', 'seller']);
        $reviews   = $product->reviews()->with('user')->latest()->get();
        $likes     = $reviews->where('vote', 'like')->count();
        $dislikes  = $reviews->where('vote', 'dislike')->count();
        $userReview = auth()->check()
            ? $reviews->firstWhere('user_id', auth()->id())
            : null;

        return view('products.show', compact('product', 'reviews', 'likes', 'dislikes', 'userReview'));
    }

    // Jauna produkta izveides forma
    public function create()
    {
    }

    // Saglabā jauno produktu ar attēlu
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'price'           => 'required|numeric|min:0.01',
            'category'        => 'nullable|string|max:100',
            'category_custom' => 'nullable|string|max:100',
            'stock'           => 'required|integer|min:1|max:9999',
            'seller_full_name'=> 'required|string|max:255',
            'contact_email'   => 'nullable|email|max:255',
            'contact_phone'   => 'nullable|string|max:50',
            'address'         => 'nullable|string|max:255',
            'delivery_days'   => 'nullable|integer|min:1|max:365',
            'image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $category = $request->category === '_other'
            ? trim($request->category_custom ?? '')
            : $request->category;

        $phone = null;
        if ($request->filled('phone_code') && $request->filled('phone_number')) {
            $phone = $request->phone_code . $request->phone_number;
        } elseif ($request->filled('phone_number')) {
            $phone = $request->phone_number;
        }

        $path = $request->file('image')?->store('products', 'public');

        $product = Product::create([
            'user_id'          => auth()->id(),
            'seller_full_name' => $request->seller_full_name,
            'title'            => $request->title,
            'description'   => $request->description,
            'price'         => $request->price,
            'currency'      => 'eur',
            'category'      => $category ?: null,
            'stock'         => (int) $request->stock,
            'contact_email' => $request->contact_email ?: null,
            'contact_phone' => $phone,
            'address'       => $request->address,
            'delivery_days' => $request->delivery_days ? (int) $request->delivery_days : null,
            'image_path'    => $path,
            'status'        => 'active',
        ]);

        return redirect()->route('products.show', $product)
            ->with('success', 'Produkts veiksmīgi pievienots!');
    }

    // Uzsāk Stripe maksājumu sesiju produkta iegādei
    public function buy(Request $request, Product $product)
    {
        $user = auth()->user();

        if ($product->user_id == $user->id) {
            return redirect()->route('products.show', $product)
                ->with('error', 'Jūs nevarat iegādāties savu produktu.');
        }

        if ($product->status !== 'active') {
            return redirect()->route('products.show', $product)
                ->with('error', 'Produkts nav pieejams.');
        }

        if ($product->stock < 1) {
            return redirect()->route('products.show', $product)
                ->with('error', 'Produkts ir izpārdots.');
        }

        $order = Order::create([
            'buyer_id'   => $user->id,
            'product_id' => $product->id,
            'amount'     => $product->price,
            'currency'   => $product->currency ?? 'eur',
            'status'     => 'pending',
        ]);

        try {
            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency'     => $product->currency ?? 'eur',
                        'product_data' => ['name' => $product->title],
                        'unit_amount'  => (int) round($product->price * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode'        => 'payment',
                'success_url' => route('products.purchase_success', ['order' => $order->id]) . '&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('products.purchase_cancel', ['order' => $order->id]),
                'metadata'    => ['order_id' => (string) $order->id],
            ]);
        } catch (\Throwable $e) {
            \Log::error("Stripe checkout session error: {$e->getMessage()}");
            $order->delete();
            return redirect()->route('products.show', $product)
                ->with('error', 'Maksājumu sistēmas kļūda.');
        }

        return redirect()->away($session->url);
    }

    // Apstrādā veiksmīgu produkta pirkumu un nosūta apstiprinājuma e-pastu
    public function purchaseSuccess(Request $request)
    {
        $order = Order::find($request->order);

        if ($order && $order->status === 'pending') {
            DB::transaction(function () use ($order) {
                $order->status = 'paid';
                $order->save();

                // Decrement stock atomically; if it reaches 0, mark product sold
                $product = Product::lockForUpdate()->find($order->product_id);
                if ($product && $product->stock > 0) {
                    $product->decrement('stock');
                    if ($product->fresh()->stock <= 0) {
                        $product->status = 'sold';
                        $product->save();
                    }
                }
            });

            try {
                $order->load('product', 'buyer');
                if ($order->buyer?->email) {
                    Mail::to($order->buyer->email)->send(new OrderConfirmed($order));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send OrderConfirmed email: ' . $e->getMessage());
            }
        }

        return redirect()->route('orders.index')
            ->with('success', 'Pirkums veiksmīgs! Pasūtījuma apstiprinājums nosūtīts uz jūsu e-pastu.');
    }

    // Atceļ gaidošo pasūtījumu
    public function purchaseCancel(Request $request)
    {
        $order = Order::find($request->order);

        if ($order && $order->status === 'pending') {
            $order->status = 'cancelled';
            $order->save();
        }

        return redirect()->route('products.show', $order->product)
            ->with('error', 'Pasūtījums tika atcelts.');
    }

    // Rāda lietotāja pasūtījumu vēsturi
    public function myOrders(Request $request)
    {
        $orders = Order::where('buyer_id', auth()->id())
            ->with('product')
            ->latest()
            ->paginate(12);

        return view('orders.index', compact('orders'));
    }

    // Rāda lietotāja pašu produktu sarakstu
    public function myProducts(Request $request)
    {
        $products = Product::where('user_id', auth()->id())
            ->latest()
            ->paginate(12);

        return view('products.my-products', compact('products'));
    }

    // Papildina produkta krājumu
    public function restock(Request $request, Product $product)
    {
        if ($product->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1|max:9999',
        ]);

        $product->increment('stock', (int) $request->quantity);

        // If product was sold out, reactivate it
        if ($product->status === 'sold') {
            $product->status = 'active';
            $product->save();
        }

        return back()->with('success', 'Noliktava atjaunināta! Pievienots: ' . $request->quantity . ' gb.');
    }
}

