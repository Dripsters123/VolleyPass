<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'vote' => 'required|in:like,dislike',
        ]);

        $userId = auth()->id();

        // Seller cannot review own product
        if ($product->user_id === $userId) {
            return back()->with('error', 'Jūs nevarat vērtēt savu produktu.');
        }

        ProductReview::updateOrCreate(
            ['product_id' => $product->id, 'user_id' => $userId],
            ['vote' => $request->vote, 'comment' => null]
        );

        return back();
    }

    public function destroy(Product $product)
    {
        $review = ProductReview::where('product_id', $product->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $review->delete();

        return back()->with('success', 'Atsauksme dzēsta.');
    }
}
