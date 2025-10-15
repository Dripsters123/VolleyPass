<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id', 'user_id', 'type', 'coins', 'status',
        'related_type', 'related_id', 'note'
    ];

    protected $casts = [
        'coins' => 'integer',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function related()
    {
        return $this->morphTo();
    }
}
