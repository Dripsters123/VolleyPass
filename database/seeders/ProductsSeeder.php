<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\ProductRequest;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', '!=', 'admin')->get();
        if ($users->count() < 4) {
            User::factory()->count(6)->create();
            $users = User::where('role', '!=', 'admin')->get();
        }

  
        $this->ensurePlaceholderExists();

        $titlePrefixes = ['Match', 'Volley', 'Arena', 'Fan', 'Team', 'Pro', 'Sport', 'Club'];
        $titleItems = ['Scarf', 'Jersey', 'Bottle', 'Poster', 'Cap', 'Bag', 'Ball', 'Sticker Pack'];
        $descriptions = [
            'Official quality merchandise for match day and training sessions.',
            'Durable and lightweight item designed for frequent use.',
            'Great fan product with a clean look and comfortable fit.',
            'Limited seeded catalog item with reliable quality and finish.',
        ];
        $requestTypes = ['create_product', 'update_product', 'delete_request', 'price_change'];
        $requestStatuses = ['pending', 'approved', 'rejected'];

        for ($i = 0; $i < 30; $i++) {
            $seller = $users->random();
            $title = $titlePrefixes[array_rand($titlePrefixes)].' '.$titleItems[array_rand($titleItems)].' #'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
            $price = round(random_int(300, 12000) / 100, 2);
            $description = $descriptions[array_rand($descriptions)].' Ref: '.Str::upper(Str::random(6));

            try {
                $svg = $this->generateProductSvg($title);
                $slug = Str::slug($title);
                $filename = 'product_images/product_' . now()->format('Ymd_His') . '_' . uniqid() . '_' . $slug . '.svg';
                Storage::disk('public')->put($filename, $svg);
                $imagePath = $filename;
            } catch (\Throwable $e) {
                \Log::warning("ProductsSeeder: failed to generate image for title '{$title}': " . $e->getMessage());
                $imagePath = 'product_images/placeholder.svg';
            }

            $product = Product::create([
                'user_id'    => $seller->id,
                'title'      => $title,
                'description'=> $description,
                'price'      => $price,
                'currency'   => 'eur',
                'status'     => 'active',
                'image_path' => $imagePath,
            ]);

            if (random_int(1, 100) <= 20) {
                $buyerCandidates = $users->filter(fn($u) => $u->id !== $seller->id)->values();
                if ($buyerCandidates->isNotEmpty()) {
                    $buyer = $buyerCandidates->random();
                    $order = Order::create([
                        'buyer_id' => $buyer->id,
                        'product_id' => $product->id,
                        'amount' => $product->price,
                        'currency' => $product->currency,
                        'status' => 'paid',
                        'stripe_payment_intent' => 'seeded-intent-' . Str::random(12),
                    ]);

                    $product->status = 'sold';
                    $product->save();

                    try {
                        $coinsRate = floatval(config('app.product_sale_coins_rate', 1.0));
                        $coins = round($product->price * $coinsRate, 2);
                        $wallet = Wallet::firstOrCreate(['user_id' => $seller->id], ['balance' => 0]);

                        if (method_exists($wallet, 'credit')) {
                            $wallet->credit($coins, 'earned_sale', $seller->id, $order, 'Seeded product sale');
                        } else {
                            $wallet->balance += $coins;
                            $wallet->save();
                            if (DB::getSchemaBuilder()->hasTable('wallet_transactions')) {
                                DB::table('wallet_transactions')->insert([
                                    'wallet_id' => $wallet->id,
                                    'user_id' => $seller->id,
                                    'type' => 'earned_sale',
                                    'amount' => $coins,
                                    'status' => 'completed',
                                    'related_type' => get_class($order),
                                    'related_id' => $order->id,
                                    'note' => 'Seeded product sale',
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    } catch (\Throwable $e) {
                        \Log::warning("ProductsSeeder: failed to credit seller {$seller->id} for product {$product->id}: " . $e->getMessage());
                    }
                }
            }

            ProductRequest::create([
                'user_id'     => $users->random()->id,
                'product_id'  => $product->id,
                'request_type'=> $requestTypes[array_rand($requestTypes)],
                'title'       => $product->title,
                'description' => $product->description,
                'price'       => $product->price,
                'currency'    => $product->currency,
                'status'      => $requestStatuses[array_rand($requestStatuses)],
               
                'image_path'  => $product->image_path,
            ]);
        }
    }

   
    private function ensurePlaceholderExists(): void
    {
        $placeholderPath = 'product_images/placeholder.svg';
        if (!Storage::disk('public')->exists($placeholderPath)) {
            try {
                $svg = $this->generateProductSvg('PRODUCT');
                Storage::disk('public')->put($placeholderPath, $svg);
            } catch (\Throwable $e) {
                \Log::warning("ProductsSeeder: failed to create placeholder image: " . $e->getMessage());
              
            }
        }
    }

    private function generateProductSvg(string $text): string
    {
        $safe = htmlspecialchars(Str::upper(substr($text, 0, 12)), ENT_QUOTES, 'UTF-8');
        $bg = sprintf("#%06x", random_int(0, 0xffffff));
        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="400" viewBox="0 0 600 400">
  <rect width="100%" height="100%" rx="12" fill="{$bg}"/>
  <text x="50%" y="50%" font-family="Arial, Helvetica, sans-serif" font-size="28" fill="#ffffff" dominant-baseline="middle" text-anchor="middle">{$safe}</text>
</svg>
SVG;
    }
}
