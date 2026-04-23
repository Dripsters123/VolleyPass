<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'request_type',
        'match_id',
        'home_team',
        'away_team',
        'start_time',
        'end_time',
        'players_per_team',
        'home_players',
        'away_players',
        'status',
        'home_coach',
        'away_coach',
        'judges',
        'location',
        'home_logo',
        'away_logo',
        'score_home',
        'score_away',
        'arena_name',
        'arena_layout',
        'arena_elements',
        'arena_width',
        'arena_height',
        'ticket_price',
    ];

    protected $casts = [
        'home_players' => 'array',
        'away_players' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'judges' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function match()
    {
        return $this->belongsTo(\App\Models\VolleyballMatch::class, 'match_id');
    }
}
