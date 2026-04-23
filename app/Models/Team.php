<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'logo',
        'players_per_team',
        'players',
        'coach',
    ];

    protected $casts = [
        'players' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
