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
        return view('products.create');
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

        // Kategorija: ja izvēlēts "_other", izmanto brīvi ievadīto vērtību
        $category = $request->category === '_other'
            ? trim($request->category_custom ?? '')
            : $request->category;

        // Apvieno tālruņa kodu un numuru vienā laukā, ja abi aizpildīti
        $phone = null;
        if ($request->filled('phone_code') && $request->filled('phone_number')) {
            $phone = $request->phone_code . $request->phone_number;
        } elseif ($request->filled('phone_number')) {
            $phone = $request->phone_number;
        }

        // Saglabā augšupielādēto attēlu publiskajā storage mapē 'products/'
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

    // Atgriež produkta rediģēšanas formas skatu
    public function edit(Product $product)
    {
        if ($product->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403);
        }

        return view('products.edit', compact('product'));
    }

    // Atjaunina produkta informāciju
    public function update(Request $request, Product $product)
    {
        if ($product->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'price'           => 'required|numeric|min:0.01',
            'category'        => 'nullable|string|max:100',
            'category_custom' => 'nullable|string|max:100',
            'stock'           => 'required|integer|min:0|max:9999',
            'seller_full_name'=> 'nullable|string|max:255',
            'contact_email'   => 'nullable|email|max:255',
            'contact_phone'   => 'nullable|string|max:50',
            'address'         => 'nullable|string|max:255',
            'delivery_days'   => 'nullable|integer|min:1|max:365',
            'image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'status'          => 'nullable|in:active,inactive,sold',
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

        $updateData = [
            'title'            => $request->title,
            'description'      => $request->description,
            'price'            => $request->price,
            'category'         => $category ?: null,
            'stock'            => (int) $request->stock,
            'seller_full_name' => $request->seller_full_name ?: $product->seller_full_name,
            'contact_email'    => $request->contact_email ?: $product->contact_email,
            'contact_phone'    => $phone ?: $product->contact_phone,
            'address'          => $request->address ?: $product->address,
            'delivery_days'    => $request->delivery_days ? (int) $request->delivery_days : $product->delivery_days,
        ];

        // Tikai administrators drīkst mainīt produkta statusu
        if (auth()->user()->role === 'admin') {
            $updateData['status'] = $request->status ?? $product->status;
        }

        // Ja pievienots jauns attēls, dzēš veco un saglabā jauno
        if ($request->hasFile('image')) {
            if ($product->image_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image_path);
            }
            $updateData['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($updateData);

        return redirect()->route('products.show', $product)
            ->with('success', 'Produkts veiksmīgi atjaunināts!');
    }

    // Uzsāk Stripe maksājumu sesiju produkta iegādei
    public function buy(Request $request, Product $product)
    {
        $user = auth()->user(); // Iegūst pašreizējo autentificēto lietotāju

        // Validācija: lietotājs nedrīkst pirkt savu paša produktu
        if ($product->user_id == $user->id) {
            return redirect()->route('products.show', $product) // Novirza atpakaļ uz produkta lapu
                ->with('error', 'Jūs nevarat iegādāties savu produktu.'); // Pievienots kļūdas ziņojums
        }

        // Pārbauda vai produkts ir aktīvs un pieejams pirkumam
        if ($product->status !== 'active') {
            return redirect()->route('products.show', $product) // Novirza atpakaļ uz produkta lapu
                ->with('error', 'Produkts nav pieejams.'); // Pievienots kļūdas ziņojums
        }

        // Pārbauda produkta daudzumu noliktavā (stock > 0)
        if ($product->stock < 1) {
            return redirect()->route('products.show', $product) // Novirza atpakaļ uz produkta lapu
                ->with('error', 'Produkts ir izpārdots.'); // Pievienots kļūdas ziņojums
        }

        // Izveido jaunu pasūtījuma ierakstu datubāzē ar statusu 'pending' pirms Stripe izsaukuma
        $order = Order::create([
            'buyer_id'   => $user->id,          // Pircēja lietotāja ID
            'product_id' => $product->id,       // Iegādājamā produkta ID
            'amount'     => $product->price,    // Produkta cena, ko jāmaksā
            'currency'   => $product->currency ?? 'eur', // Valūta (noklusējuma valūta: EUR)
            'status'     => 'pending',          // Statuss: gaida maksājuma apstiprinājumu
        ]);

        // Mēģina izveidot Stripe Checkout sesiju maksājumam
        try {
            $session = StripeSession::create([
                'payment_method_types' => ['card'], // Atbalstītā maksājuma metode: karte
                'line_items' => [[
                    'price_data' => [
                        'currency'     => $product->currency ?? 'eur', // Valūta maksājumam
                        'product_data' => ['name' => $product->title], // Produkta nosaukums Stripe pirkuma lapā
                        'unit_amount'  => (int) round($product->price * 100), // Cena centos (Stripe prasība)
                    ],
                    'quantity' => 1, // Viena prece uz vienu pirkumu
                ]],
                'mode'        => 'payment', // Vienreizējs maksājums
                'success_url' => route('products.purchase_success', ['order' => $order->id]) . '&session_id={CHECKOUT_SESSION_ID}', // URL pēc veiksmīga maksājuma
                'cancel_url'  => route('products.purchase_cancel', ['order' => $order->id]), // URL ja lietotājs atceļ maksājumu
                'metadata'    => ['order_id' => (string) $order->id], // Metadati: pasūtījuma ID webhook apstrādei
            ]);
        } catch (\Throwable $e) {
            \Log::error("Stripe checkout session error: {$e->getMessage()}"); // Ieraksta kļūdu sistēmas žurnālā
            $order->delete(); // Dzēš izveidoto pasūtījumu, jo Stripe sesija neizdeviās
            return redirect()->route('products.show', $product)
                ->with('error', 'Maksājumu sistēmas kļūda.'); // Pievienots kļūdas ziņojums
        }

        return redirect()->away($session->url); // Novirza lietotāju uz Stripe checkout lapu
    }

    // Apstrādā veiksmīgu produkta pirkumu un nosūta apstiprinājuma e-pastu
    public function purchaseSuccess(Request $request)
    {
        $order = Order::find($request->order); // Atrod pasūtījumu datubāzē pēc URL parametra 'order'

        // Apstrādā tikai ja pasūtījums eksistē un vēl ir 'pending' (aizsardzība pret dubultu apstrādi)
        if ($order && $order->status === 'pending') {
            DB::transaction(function () use ($order) { // Sāk datubāzes transakciju
                $order->status = 'paid'; // Nomaina pasūtījuma statusu uz 'paid'
                $order->save(); // Saglabā statusu datubāzē

                // Pārbauda un atjaunina produkta daudzumu noliktavā, izmantojot "SELECT ... FOR UPDATE" bloķēšanu, lai izvairītos no "konkurences" problēmām
                $product = Product::lockForUpdate()->find($order->product_id);
                if ($product && $product->stock > 0) { // Pārbauda vai produkts ir vēl ir pieejams noliktavā
                    $product->decrement('stock'); // Samazina produktu daudzumu par 1
                    if ($product->fresh()->stock <= 0) { // Pārlasa produkta daudzumu pēc samazināšanas
                        $product->status = 'sold'; // Ja krājums sasniedz 0 — atzīmē produktu kā 'sold' jeb pārdotu
                        $product->save(); // Saglabā jauno statusu datubāzē
                    }
                }
            });

            // Mēģina nosūtīt apstiprinājuma e-pastu pircējam
            try {
                $order->load('product', 'buyer'); // Ielādē saistītos modeļus e-pasta saturam
                if ($order->buyer?->email) { // Pārbauda vai pircējam ir e-pasta adrese
                    Mail::to($order->buyer->email)->send(new OrderConfirmed($order)); // Nosūta apstiprinājuma vēstuli
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send OrderConfirmed email: ' . $e->getMessage()); // Ieraksta kļūdu žurnālā
            }
        }

        return redirect()->route('orders.index') // Novirza uz lietotāja pasūtījumu vēsturi
            ->with('success', 'Pirkums veiksmīgs! Pasūtījuma apstiprinājums nosūtīts uz jūsu e-pastu.'); //  Paziņojums par veiksmīgu pirkumu
    }

    // Atceļ pasūtījumu
    public function purchaseCancel(Request $request)
    {
        $order = Order::find($request->order); // Atrod pasūtījumu datubāzē pēc URL parametra 'order'

        // Atceļ pasūtījumu tikai ja tas vēl ir 'pending' — ja 'paid' jeb samaksāts, to nemaina
        if ($order && $order->status === 'pending') {
            $order->status = 'cancelled'; // Nomaina statusu uz 'cancelled' jeb atcelts
            $order->save(); // Saglabā jauno statusu datubāzē
        }

        return redirect()->route('products.show', $order->product) // Novirza atpakaļ uz produkta lapu
            ->with('error', 'Pasūtījums tika atcelts.'); // Pievienots informācijas ziņojums
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

        // Ja produkts bija atzīmēts kā 'sold' un tiek papildināts ar jauno vērtību, maina statusu atpakaļ uz 'active'
        if ($product->status === 'sold') {
            $product->status = 'active';
            $product->save();
        }

        return back()->with('success', 'Noliktava atjaunināta! Pievienots: ' . $request->quantity . ' gb.');
    }
}

