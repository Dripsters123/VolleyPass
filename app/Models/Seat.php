<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    protected $fillable = [
        'match_id', 'seat_number', 'is_taken', 'is_fake', 'user_id'
    ];

    public function match() {
    return $this->belongsTo(VolleyballMatch::class, 'match_id');
}


    public function user() {
        return $this->belongsTo(User::class);
    }
}
