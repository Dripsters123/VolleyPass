<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Arena extends Model
{
    protected $fillable = [
        'name',
        'description',
        'user_id',
        'layout',
        'elements',
        'width',
        'height',
        'is_public',
    ];

    protected $casts = [
        'layout' => 'array',
        'elements' => 'array',
        'is_public' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function favoritedBy(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'arena_favorites')->withTimestamps();
    }

    public function isFavoritedBy(int $userId): bool
    {
        return $this->favoritedBy()->where('user_id', $userId)->exists();
    }

    public function matches()
    {
        return $this->hasMany(VolleyballMatch::class, 'arena_id');
    }

    /**
     * Get the elements as a collection for easier manipulation
     */
    protected function elementsCollection(): Attribute
    {
        return Attribute::get(function () {
            return collect($this->elements ?? []);
        });
    }

    /**
     * Get seats from elements
     */
    public function getSeats()
    {
        return $this->elementsCollection->where('type', 'seat');
    }

    /**
     * Get court element
     */
    public function getCourt()
    {
        return $this->elementsCollection->where('type', 'court')->first();
    }

    /**
     * Generate seats for a match using this arena layout
     */
    public function generateSeatsForMatch(VolleyballMatch $match, $price = null): int
    {
        $match->seats()->delete();

        $seats = $this->getSeats();
        $created = 0;

        foreach ($seats as $seatData) {
            $seat = Seat::create([
                'match_id' => $match->id,
                'seat_number' => $seatData['id'] ?? "seat-{$seatData['x']}-{$seatData['y']}",
                'side' => $seatData['side'] ?? 'custom',
                'row' => $seatData['row'] ?? 1,
                'number' => $seatData['number'] ?? $created + 1,
                'price' => $price ?? $seatData['price'] ?? $match->ticket_price ?? 10.0,
                'ticket_id' => null,
                'user_id' => null,
                'reserved_by' => null,
                'reserved_until' => null,
            ]);
            $created++;
        }

        return $created;
    }
}
