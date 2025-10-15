<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'currency',
        'status',
        'image_path',
    ];
    public function requests()
{
    return $this->hasMany(ProductRequest::class);
}

}
