<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Seat extends Model
{
    protected $fillable = [
        'match_id',
        'ticket_id',
        'seat_number',
        'side',
        'row',
        'number',
        'price',
        'reserved_by',
        'reserved_until',
        'user_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'reserved_until' => 'datetime',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(VolleyballMatch::class, 'match_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function reservedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'reserved_by');
    }

    public function getIsTakenAttribute(): bool
    {
        return $this->ticket_id !== null;
    }
    public function isReserved(): bool
    {
        if ($this->reserved_by === null) return false;
        if ($this->reserved_until === null) return true;
        return Carbon::now()->lessThanOrEqualTo($this->reserved_until);
    }
     public function setReservedUntilAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['reserved_until'] = null;
            return;
        }

        if ($value instanceof Carbon) {
            $dt = $value;
        } else {
            try {
                $dt = Carbon::parse($value);
            } catch (\Throwable $e) {
                $this->attributes['reserved_until'] = null;
                return;
            }
        }

        $this->attributes['reserved_until'] = $dt->toDateTimeString();
    }
     protected function isReservedUntilFuture(): Attribute
    {
        return Attribute::get(function () {
            if ($this->reserved_until === null) return false;
            return Carbon::now()->lessThanOrEqualTo($this->reserved_until);
        });
    }
    
}
