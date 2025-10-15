<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','coins'];

    protected $casts = [
        'coins' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function debitCoins(int $coins, string $type = 'debit', array $meta = [])
    {
        if ($coins < 0) throw new \InvalidArgumentException("Coins must be positive");
        if (intval($this->coins) < $coins) throw new \Exception('Insufficient coins');

        $this->coins = intval($this->coins) - $coins;
        $this->save();

        return $this->transactions()->create(array_merge([
            'user_id' => $this->user_id,
            'type' => $type,
            'coins' => -1 * $coins,
            'status' => $meta['status'] ?? 'completed',
            'note' => $meta['note'] ?? null,
            'related_type' => $meta['related_type'] ?? null,
            'related_id' => $meta['related_id'] ?? null,
        ], $meta));
    }

    public function creditCoins(int $coins, string $type = 'credit', array $meta = [])
    {
        if ($coins < 0) throw new \InvalidArgumentException("Coins must be positive");

        $this->coins = intval($this->coins) + $coins;
        $this->save();

        return $this->transactions()->create(array_merge([
            'user_id' => $this->user_id,
            'type' => $type,
            'coins' => $coins,
            'status' => $meta['status'] ?? 'completed',
            'note' => $meta['note'] ?? null,
            'related_type' => $meta['related_type'] ?? null,
            'related_id' => $meta['related_id'] ?? null,
        ], $meta));
    }
}
