<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\MatchRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductRequestController extends Controller
{
  
    public function index()
    {
        $requests = ProductRequest::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('product_requests.index', compact('requests'));
    }

    public function create()
    {
        return view('product_requests.create');
    }

  
    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_type' => 'nullable|in:create_product,update_product,delete_request,price_change',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:5120',
            'product_id' => 'required_if:request_type,update_product,delete_request,price_change|nullable|exists:products,id',
        ]);

        $data = [
            'user_id' => Auth::id(),
            'request_type' => $validated['request_type'] ?? 'create_product',
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'currency' => 'eur',
            'status' => 'pending',
            'product_id' => in_array($validated['request_type'] ?? 'create_product', ['update_product','delete_request','price_change'])
                ? ($validated['product_id'] ?? null)
                : null,
        ];

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('product_request_images', 'public');
        }

        ProductRequest::create($data);

        return redirect()->route('product_requests.index')
            ->with('success', 'Jūsu pieprasījums tika iesniegts administrācijai.');
    }


    public function edit(ProductRequest $productRequest)
    {
        if ($productRequest->user_id !== Auth::id() || $productRequest->status !== 'pending') {
            abort(403);
        }

        return view('product_requests.edit', compact('productRequest'));
    }


    public function update(Request $request, ProductRequest $productRequest)
    {
        if ($productRequest->user_id !== Auth::id() || $productRequest->status !== 'pending') {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:5120',
        ]);

        $productRequest->fill($validated);

        if ($request->hasFile('image')) {
            if ($productRequest->image_path) {
                Storage::disk('public')->delete($productRequest->image_path);
            }
            $productRequest->image_path = $request->file('image')->store('product_request_images', 'public');
        }

        $productRequest->save();

        return redirect()->route('product_requests.index')
            ->with('success', 'Pieprasījums atjaunināts.');
    }

    public function show(ProductRequest $productRequest)
    {
        return view('admin.product_requests.show', compact('productRequest'));
    }

    public function editForAdmin(ProductRequest $productRequest)
    {
        if ($productRequest->status !== 'pending') {
            return redirect()->route('admin.product_requests.show', $productRequest)
                ->with('error', 'Šis pieprasījums jau ir apstrādāts.');
        }

        return view('admin.product_requests.edit', compact('productRequest'));
    }

    public function approve(Request $request, ProductRequest $productRequest)
    {
        if ($productRequest->status !== 'pending') {
            return back()->with('error', 'Šis pieprasījums jau apstrādāts.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0.01',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:5120',
        ]);

        if ($request->hasFile('image')) {
            if ($productRequest->image_path) {
                Storage::disk('public')->delete($productRequest->image_path);
            }
            $productRequest->image_path = $request->file('image')->store('product_request_images', 'public');
        }

        $productRequest->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'status' => 'approved',
        ]);

        $product = Product::create([
            'user_id' => $productRequest->user_id,
            'title' => $productRequest->title,
            'description' => $productRequest->description,
            'price' => $productRequest->price,
            'currency' => $productRequest->currency,
            'status' => 'active',
            'image_path' => $productRequest->image_path,
        ]);

        $productRequest->update(['product_id' => $product->id]);

        return redirect()
            ->route('admin.product_requests.show', $productRequest)
            ->with('success', 'Produkts veiksmīgi izveidots un pieprasījums apstiprināts.');
    }

  public function reject($id)
{
    \Log::info('Reject request received', [
        'url' => request()->fullUrl(),
        'payload' => request()->all(),
    ]);

    $req = ProductRequest::find($id);

    if (! $req) {
        \Log::warning("Reject attempted but ProductRequest not found", ['id' => $id]);
        return redirect()->route('admin.match_requests.inbox')
            ->with('error', "Pieprasījums #{$id} nav atrasts.");
    }

    try {
        $req->update(['status' => 'rejected']);
        \Log::info("ProductRequest rejected", ['id' => $id, 'user_id' => auth()->id()]);
    } catch (\Throwable $e) {
        \Log::error("ProductRequest reject failed", ['id' => $id, 'error' => $e->getMessage()]);
        return redirect()->route('admin.match_requests.inbox')
            ->with('error', 'Neizdevās noraidīt pieprasījumu.');
    }

    return redirect()->route('admin.match_requests.inbox')
        ->with('success', "Produkta pieprasījums #{$id} noraidīts.");
}


}
