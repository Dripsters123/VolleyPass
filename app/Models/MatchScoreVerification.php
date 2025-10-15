<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchScoreVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'user_id',
        'home_score',
        'away_score',
        'approved',
        'approvals',
        'confirmations',
        'status',
    ];

    protected $casts = [
        'confirmations' => 'array',
        'approved' => 'boolean',
    ];

    public function match()
    {
        return $this->belongsTo(VolleyballMatch::class, 'match_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
