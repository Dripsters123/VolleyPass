<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\Wallet;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        $query = Product::query()->where('status', 'active')->orderBy('created_at', 'desc');

        if ($request->filled('mine') && auth()->check()) {
            $query->where('user_id', auth()->id());
        }

        $products = $query->paginate(12);
        return view('products.index', compact('products'));
    }

    public function show(Product $product)
    {
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
            'price' => 'required|numeric|min:0.01',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:5120',
        ]);

        $product = Product::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'currency' => 'eur',
            'status' => 'active',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store("product_images", 'public');
            $product->image_path = $path;
            $product->save();
        }

        return redirect()->route('products.show', $product)->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:5120',
        ]);

        $product->update($request->only(['title','description','price']));

        if ($request->hasFile('image')) {
            if ($product->image_path) Storage::disk('public')->delete($product->image_path);
            $path = $request->file('image')->store("product_images", 'public');
            $product->image_path = $path;
            $product->save();
        }

        return redirect()->route('products.show', $product)->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        if ($product->image_path) Storage::disk('public')->delete($product->image_path);

        $product->status = 'removed';
        $product->save();

        return redirect()->route('products.index')->with('success', 'Product removed.');
    }

    public function buy(Request $request, Product $product)
    {
        if ($product->status !== 'active') {
            return response()->json(['error' => 'Product is not available'], 400);
        }

        $user = $request->user();
        if (! $user) return response()->json(['error'=>'Unauthenticated'],401);

        $order = Order::create([
            'buyer_id' => $user->id,
            'product_id' => $product->id,
            'amount' => $product->price,
            'currency' => $product->currency ?? 'eur',
            'status' => 'pending',
        ]);

        try {
            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $product->currency ?? 'eur',
                        'product_data' => ['name' => $product->title],
                        'unit_amount' => (int) round($product->price * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('products.purchase_success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('products.purchase_cancel'),
                'metadata' => [
                    'order_id' => (string) $order->id,
                ],
            ]);
        } catch (\Throwable $e) {
            \Log::error('Product buy: Stripe session creation failed: '.$e->getMessage());
            return response()->json(['error' => 'Payment provider error'], 500);
        }

        return response()->json(['url' => $session->url]);
    }

    public function purchaseSuccess(Request $request)
    {
        return redirect()->route('products.index')->with('success', 'Payment completed — order processing.');
    }

    public function purchaseCancel()
    {
        return view('products.purchase_cancel');
    }
}
