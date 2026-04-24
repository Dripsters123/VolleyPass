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
use App\Models\ProductReview;

class ProductsSeeder extends Seeder
{
    /**
     * Realistic volleyball merchandise catalog.
     */
    private array $catalog = [
        ['title' => 'Mikasa V200W Sacensību bumba',      'price' => 89.99,  'category' => 'ball',    'desc' => 'Oficiālā FIVB sacensību volejbola bumba. Ādas virsma ar 18 panelēm nodrošina izcilu kontroli un stabilu lidojumu.'],
        ['title' => 'Asics Gel-Rocket 10 apavi',         'price' => 74.95,  'category' => 'shoes',   'desc' => 'Viegls un elpojošs volejbola apavs ar GEL amortizācijas tehnoloģiju. Ideāli piemēroti ātrām kustībām laukumā.'],
        ['title' => 'Molten V5M5000 treniņu bumba',      'price' => 54.90,  'category' => 'ball',    'desc' => 'Augstas kvalitātes sintētiskās ādas bumba, piemērota iekštelpu treniņiem un sacensībām.'],
        ['title' => 'VolleyPass komandas krekls (zils)',  'price' => 34.99,  'category' => 'jersey',  'desc' => 'Elpojošs poliestera krekls ar drukātu VolleyPass logotipu. Pieejams S–XXL izmēros.'],
        ['title' => 'Mizuno Thunder Blade Z boksa',      'price' => 109.00, 'category' => 'shoes',   'desc' => 'Profesionālais volejbola apavs ar augstu stabilitāti un neslīdošu zoli. Ideāls sacensībām.'],
        ['title' => 'Ceļgalu aizsargi (pāris)',          'price' => 19.99,  'category' => 'pads',    'desc' => 'Elastīgi neoprēna ceļgalu aizsargi ar papildu polsterējumu. Sargā no lēcienu traumām.'],
        ['title' => 'VolleyPass fanu šalle',             'price' => 14.50,  'category' => 'fan',     'desc' => 'Silta un mīksta akrila šalle ar VolleyPass krāsām. Ideāla faniem tribīnēs.'],
        ['title' => 'Treniņu antenas komplekts',         'price' => 45.00,  'category' => 'net',     'desc' => 'Profesionāls antenu komplekts tīkla apzīmēšanai. Stingrs un izturīgs fiberglass.'],
        ['title' => 'Nike Hyperspike apavi (balti)',      'price' => 119.00, 'category' => 'shoes',   'desc' => 'Nike volejbola apavs ar Zoom Air pēdas paketi un izcilas saķeres zoli.'],
        ['title' => 'Komandas soma (liela)',              'price' => 49.99,  'category' => 'bag',     'desc' => 'Ietilpīga 50 L sporta soma ar speciālu nodalījumu apaviem un ventilācijas sistēmu.'],
        ['title' => 'Volejbola tīkls (sacensību)',       'price' => 139.00, 'category' => 'net',     'desc' => 'FIVB standartiem atbilstošs sacensību tīkls. Izturīgs poliamīda auklas audums ar nerūsējošiem tērauda kabeļiem.'],
        ['title' => 'Libero krekls (dzeltens)',          'price' => 39.99,  'category' => 'jersey',  'desc' => 'Kontrasta krāsas krekls libero pozīcijai. Viegls un elpojošs audums ar reflektējošu logotipu.'],
        ['title' => 'Sienas plakāts "Smash" A1',        'price' => 12.99,  'category' => 'fan',     'desc' => 'Augstas izšķirtspējas impresija uz matēta papīra. Formāts 594×841 mm.'],
        ['title' => 'Treniņu kones (20 gab.)',           'price' => 22.00,  'category' => 'training','desc' => 'Spilgti krāsaini koni treniņu maršruta iezīmēšanai. Ātri uzstādāmi un nestāvoši.'],
        ['title' => 'Asics Gel-Task 2 dāmu apavi',      'price' => 64.95,  'category' => 'shoes',   'desc' => 'Viegls sieviešu volejbola apavs ar GEL aizsardzību papēdī un AHAR nolietojumizturīgu zoli.'],
        ['title' => 'Volejbola bumbu soma (6 bumbām)',   'price' => 59.00,  'category' => 'bag',     'desc' => 'Speciāla soma bumbu transportēšanai. Ietilpst 6 standarta bumbu. Ērta pārnēsāšana.'],
        ['title' => 'VolleyPass sporta cepure',          'price' => 18.00,  'category' => 'fan',     'desc' => 'Elpojošs neilona sporta cepure ar izšūtu VolleyPass logotipu. Regulējams izmērs.'],
        ['title' => 'Plaukstu aizsargi (pāris)',         'price' => 9.99,   'category' => 'pads',    'desc' => 'Elastīgi neoprēna plaukstu aizsargi pieaugušajiem un junioriem.'],
        ['title' => 'Mikasa MVA200 Olimpiskā bumba',     'price' => 99.00,  'category' => 'ball',    'desc' => 'Olimpisko spēļu un pasaules čempionāta bumba. Dzeltens un zils krāsu dizains.'],
        ['title' => 'Treniņu pīlārs (180 cm)',          'price' => 249.00, 'category' => 'net',     'desc' => 'Regulējams tērauda pīlārs tīkla uzstādīšanai iekštelpu laukumos.'],
        ['title' => 'VolleyPass sporta dvielis',         'price' => 15.99,  'category' => 'fan',     'desc' => 'Absorbējošs mikrofibras dvielis 80×160 cm ar VolleyPass krāsām.'],
        ['title' => 'Brinco piekļuves karte (sezona)',  'price' => 189.00, 'category' => 'ticket',  'desc' => 'Sezonas abonements visiem iekštelpu mačiem. Ietver VIP sēdvietu nodalījumu.'],
        ['title' => 'Komandas formu komplekts (10 gab)','price' => 299.00, 'category' => 'jersey',  'desc' => '10 komandas formu komplekts ar pielāgotu numura un vārda druku.'],
        ['title' => 'Elastīgās jostas komplekts',       'price' => 29.99,  'category' => 'training','desc' => 'Treniņu pretestības jostas komplekts 5 dažādas intensitātēs.'],
        ['title' => 'Volejbola meistara grāmata (LV)',  'price' => 24.99,  'category' => 'fan',     'desc' => 'Latviešu valodā izdota volejbola taktiku un treneru metodik grāmata.'],
        ['title' => 'Adidas Ligra 7 apavi',             'price' => 79.95,  'category' => 'shoes',   'desc' => 'Adidas volejbola apavs ar Cloudfoam starpzoli. Ideāls iesācējiem un vidēja līmeņa spēlētājiem.'],
        ['title' => 'Laukuma marķēšanas lente (50 m)',  'price' => 12.00,  'category' => 'training','desc' => 'Noturīga, UV izturīga poliestera lente laukuma robežu atzīmēšanai.'],
        ['title' => 'Roka siksna "VolleyPass" (2 gab)', 'price' => 8.50,   'category' => 'fan',     'desc' => 'Silikona roku siksnas ar VolleyPass logotipu. Izturīgas un ērtās.'],
        ['title' => 'Profesionāla tīmekļa kamera treniņiem', 'price' => 159.00, 'category' => 'training', 'desc' => '1080p tīmekļa kamera ar platu leņķi treniņu analīzei un video ierakstam.'],
        ['title' => 'Locker Room plakāts (motivācija)', 'price' => 10.99,  'category' => 'fan',     'desc' => 'A2 formāta motivācijas plakāts ar volejbola citātu. Drukāts uz biezā papīra.'],
    ];

    private array $categoryColors = [
        'ball'     => ['bg' => '#f59e0b', 'accent' => '#92400e', 'icon' => '🏐'],
        'shoes'    => ['bg' => '#3b82f6', 'accent' => '#1e3a8a', 'icon' => '👟'],
        'jersey'   => ['bg' => '#10b981', 'accent' => '#065f46', 'icon' => '👕'],
        'bag'      => ['bg' => '#8b5cf6', 'accent' => '#4c1d95', 'icon' => '🎒'],
        'net'      => ['bg' => '#6b7280', 'accent' => '#1f2937', 'icon' => '🕸️'],
        'pads'     => ['bg' => '#ef4444', 'accent' => '#7f1d1d', 'icon' => '🦺'],
        'fan'      => ['bg' => '#ec4899', 'accent' => '#831843', 'icon' => '⭐'],
        'training' => ['bg' => '#14b8a6', 'accent' => '#134e4a', 'icon' => '🏋️'],
        'ticket'   => ['bg' => '#f97316', 'accent' => '#7c2d12', 'icon' => '🎟️'],
    ];

    public function run(): void
    {
        $users = User::where('role', '!=', 'admin')->get();
        if ($users->count() < 4) {
            User::factory()->count(6)->create();
            $users = User::where('role', '!=', 'admin')->get();
        }

        $this->ensurePlaceholderExists();

        $latvianCities = [
            'Rīga, Brīvības iela 55',
            'Rīga, Čaka iela 12',
            'Rīga, Miera iela 28',
            'Rīga, Tallinas iela 71',
            'Jūrmala, Jomas iela 14',
            'Jelgava, Akadēmijas iela 3',
            'Liepāja, Rīgas iela 42',
            'Ventspils, Jūras iela 8',
            'Jēkabpils, Brīvības iela 97',
            'Valmiera, Lāčplēša iela 5',
            'Daugavpils, Mihoelsa iela 22',
            'Rēzekne, Atbrīvošanas aleja 100',
        ];

        $requestTypes    = ['create_product', 'update_product', 'price_change'];
        $requestStatuses = ['pending', 'approved', 'rejected'];

        // Review bias per catalog index: 'liked', 'disliked', 'mixed'
        $reviewBiases = [
            'liked',    // 0  Mikasa V200W
            'liked',    // 1  Asics Gel-Rocket
            'liked',    // 2  Molten V5M5000
            'mixed',    // 3  VolleyPass krekls
            'liked',    // 4  Mizuno Thunder Blade
            'mixed',    // 5  Ceļgalu aizsargi
            'mixed',    // 6  Fanu šalle
            'mixed',    // 7  Treniņu antenas
            'liked',    // 8  Nike Hyperspike
            'mixed',    // 9  Komandas soma
            'liked',    // 10 Volejbola tīkls
            'mixed',    // 11 Libero krekls
            'mixed',    // 12 Sienas plakāts
            'mixed',    // 13 Treniņu kones
            'liked',    // 14 Asics Gel-Task 2
            'mixed',    // 15 Bumbu soma
            'mixed',    // 16 Sporta cepure
            'mixed',    // 17 Plaukstu aizsargi
            'liked',    // 18 Mikasa MVA200
            'disliked', // 19 Treniņu pīlārs (pārāk dārgs)
            'mixed',    // 20 Sporta dvielis
            'disliked', // 21 Sezonas abonements (dārgs)
            'disliked', // 22 Komandas formu komplekts (dārgs)
            'mixed',    // 23 Elastīgās jostas
            'mixed',    // 24 Grāmata
            'liked',    // 25 Adidas Ligra 7
            'mixed',    // 26 Marķēšanas lente
            'mixed',    // 27 Roku siksna
            'disliked', // 28 Profesionāla kamera (dārga/nišas)
            'mixed',    // 29 Motivācijas plakāts
        ];

        $phonePool = [
            '+37120111222',
            '+37126333444',
            '+37128555666',
            '+37129777888',
            '+37127999000',
            '+37122444555',
            '+37125678901',
            '+37124321098',
        ];

        $emailPool = [
            'volleyshop@inbox.lv',
            'sporta.preces@gmail.com',
            'parvaldnieks@volley.lv',
            'pardavejs@sporta.lv',
            'veikals@vball.lv',
        ];

        foreach ($this->catalog as $i => $item) {
            $seller = $users->random();
            $category = $item['category'];
            $colors   = $this->categoryColors[$category] ?? $this->categoryColors['fan'];

            try {
                $svg = $this->generateProductSvg($item['title'], $colors['bg'], $colors['accent'], $colors['icon']);
                $slug = Str::slug($item['title']);
                $filename = 'product_images/product_' . str_pad((string)($i + 1), 3, '0', STR_PAD_LEFT) . '_' . $slug . '.svg';
                Storage::disk('public')->put($filename, $svg);
                $imagePath = $filename;
            } catch (\Throwable $e) {
                \Log::warning("ProductsSeeder: SVG failed for '{$item['title']}': " . $e->getMessage());
                $imagePath = 'product_images/placeholder.svg';
            }

            $product = Product::create([
                'user_id'          => $seller->id,
                'seller_full_name' => trim($seller->first_name . ' ' . $seller->last_name) ?: $seller->name,
                'title'            => $item['title'],
                'description'   => $item['desc'],
                'price'         => $item['price'],
                'currency'      => 'eur',
                'status'        => 'active',
                'category'      => $item['category'],
                'stock'         => rand(5, 30),
                'contact_email' => $emailPool[array_rand($emailPool)],
                'contact_phone' => $phonePool[array_rand($phonePool)],
                'address'       => $latvianCities[array_rand($latvianCities)],
                'delivery_days' => rand(1, 7),
                'image_path'    => $imagePath,
            ]);

            // 20% chance of simulated sale
            if (random_int(1, 100) <= 20) {
                $buyerCandidates = $users->filter(fn($u) => $u->id !== $seller->id)->values();
                if ($buyerCandidates->isNotEmpty()) {
                    $buyer = $buyerCandidates->random();
                    $order = Order::create([
                        'buyer_id'               => $buyer->id,
                        'product_id'             => $product->id,
                        'amount'                 => $product->price,
                        'currency'               => $product->currency,
                        'status'                 => 'paid',
                        'stripe_payment_intent'  => 'seeded-intent-' . Str::random(12),
                    ]);
                    $product->status = 'sold';
                    $product->stock  = 0;
                    $product->save();

                    // Award coins: 5 per €1 spent
                    try {
                        $coins  = round($product->price * 5);
                        $wallet = Wallet::firstOrCreate(['user_id' => $seller->id], ['balance' => 0]);
                        if (method_exists($wallet, 'credit')) {
                            $wallet->credit($coins, 'earned_sale', $seller->id, $order, 'Seeded product sale');
                        } else {
                            $wallet->balance += $coins;
                            $wallet->save();
                            if (DB::getSchemaBuilder()->hasTable('wallet_transactions')) {
                                DB::table('wallet_transactions')->insert([
                                    'wallet_id'    => $wallet->id,
                                    'user_id'      => $seller->id,
                                    'type'         => 'earned_sale',
                                    'amount'       => $coins,
                                    'status'       => 'completed',
                                    'related_type' => get_class($order),
                                    'related_id'   => $order->id,
                                    'created_at'   => now(),
                                    'updated_at'   => now(),
                                ]);
                            }
                        }
                    } catch (\Throwable $e) {
                        \Log::warning("ProductsSeeder: wallet credit failed: " . $e->getMessage());
                    }
                }
            }

            ProductRequest::create([
                'user_id'          => $users->random()->id,
                'seller_full_name' => $product->seller_full_name,
                'product_id'       => $product->id,
                'request_type'  => $requestTypes[array_rand($requestTypes)],
                'title'         => $product->title,
                'description'   => $product->description,
                'price'         => $product->price,
                'stock'         => rand(5, 30),
                'currency'      => $product->currency,
                'category'      => $product->category,
                'contact_email' => $product->contact_email,
                'contact_phone' => $product->contact_phone,
                'address'       => $product->address,
                'delivery_days' => $product->delivery_days,
                'status'        => $requestStatuses[array_rand($requestStatuses)],
                'image_path'    => $product->image_path,
            ]);

            // Seed reviews with bias
            $bias = $reviewBiases[$i] ?? 'mixed';
            $reviewerPool = $users->filter(fn($u) => $u->id !== $seller->id)->values()->shuffle();
            $reviewCount  = match($bias) {
                'liked'    => rand(6, min(12, $reviewerPool->count())),
                'disliked' => rand(4, min(10, $reviewerPool->count())),
                default    => rand(2, min(8, $reviewerPool->count())),
            };
            foreach ($reviewerPool->take($reviewCount) as $reviewer) {
                $vote = match($bias) {
                    'liked'    => rand(1, 10) <= 8 ? 'like' : 'dislike',
                    'disliked' => rand(1, 10) <= 8 ? 'dislike' : 'like',
                    default    => rand(0, 1) ? 'like' : 'dislike',
                };
                ProductReview::create([
                    'product_id' => $product->id,
                    'user_id'    => $reviewer->id,
                    'vote'       => $vote,
                ]);
            }
        }
    }

    private function ensurePlaceholderExists(): void
    {
        $placeholderPath = 'product_images/placeholder.svg';
        if (!Storage::disk('public')->exists($placeholderPath)) {
            try {
                $svg = $this->generateProductSvg('Prece', '#6b7280', '#1f2937', '🛒');
                Storage::disk('public')->put($placeholderPath, $svg);
            } catch (\Throwable $e) {
                \Log::warning("ProductsSeeder: placeholder failed: " . $e->getMessage());
            }
        }
    }

    private function generateProductSvg(string $title, string $bgColor, string $accentColor, string $icon): string
    {
        $safeTitle  = htmlspecialchars(mb_substr($title, 0, 28), ENT_QUOTES | ENT_XML1, 'UTF-8');
        $safeIcon   = htmlspecialchars($icon, ENT_QUOTES | ENT_XML1, 'UTF-8');

        // Wrap long title into two lines
        $words = explode(' ', $safeTitle);
        $line1 = '';
        $line2 = '';
        foreach ($words as $w) {
            if (mb_strlen($line1 . ' ' . $w) <= 22) {
                $line1 = ltrim($line1 . ' ' . $w);
            } else {
                $line2 = ltrim($line2 . ' ' . $w);
            }
        }
        $line1Safe = htmlspecialchars($line1, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $line2Safe = htmlspecialchars($line2, ENT_QUOTES | ENT_XML1, 'UTF-8');

        $textY1 = $line2 ? '200' : '220';
        $textY2 = '230';

        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="400" viewBox="0 0 600 400">
  <defs>
    <linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$bgColor}"/>
      <stop offset="100%" stop-color="{$accentColor}"/>
    </linearGradient>
  </defs>
  <rect width="600" height="400" fill="url(#g)" rx="18"/>
  <rect x="20" y="20" width="560" height="360" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="1.5" rx="12"/>
  <circle cx="300" cy="160" r="70" fill="rgba(255,255,255,0.12)"/>
  <text x="300" y="178" text-anchor="middle" font-size="60">{$safeIcon}</text>
  <text x="300" y="{$textY1}" text-anchor="middle" font-family="Arial, sans-serif" font-size="24" font-weight="bold" fill="white" opacity="0.95">{$line1Safe}</text>
  <text x="300" y="{$textY2}" text-anchor="middle" font-family="Arial, sans-serif" font-size="22" fill="white" opacity="0.85">{$line2Safe}</text>
  <rect x="0" y="340" width="600" height="60" fill="rgba(0,0,0,0.25)" rx="0"/>
  <rect x="0" y="340" width="600" height="60" fill="rgba(0,0,0,0.25)" rx="0" ry="18"/>
  <text x="300" y="378" text-anchor="middle" font-family="Arial, sans-serif" font-size="14" fill="rgba(255,255,255,0.7)" letter-spacing="3">VOLLEYPASS SHOP</text>
</svg>
SVG;
    }
}