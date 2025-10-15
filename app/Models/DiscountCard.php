<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class DiscountCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'code', 'discount_percent', 'active', 'stripe_payment_intent', 'used_at'
    ];

    protected $casts = [
        'active' => 'boolean',
        'used_at' => 'datetime',
    ];

    public static function generateUniqueCode($prefix = 'DC', $len = 8)
    {
        do {
            $code = strtoupper($prefix . Str::random($len));
        } while (self::where('code', $code)->exists());

        return $code;
    }
}
