<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

   protected $fillable = [
        'user_id',
        'event_id',
        'ticket_type',
        'quantity',
        'amount_paid',
        'currency',
        'seat_number',
        'status',
        'stripe_email',
        'stripe_payment_intent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
