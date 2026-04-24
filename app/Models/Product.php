<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'seller_full_name',
        'title',
        'description',
        'price',
        'currency',
        'status',
        'image_path',
        'category',
        'stock',
        'contact',
        'contact_email',
        'contact_phone',
        'address',
        'delivery_days',
    ];

    public function isAvailable(): bool
    {
        return $this->status === 'active' && $this->stock > 0;
    }
    public function requests()
    {
        return $this->hasMany(ProductRequest::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
