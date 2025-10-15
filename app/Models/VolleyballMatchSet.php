<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VolleyballMatchSet extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'set_number',
        'home_score',
        'away_score',
        'completed',
    ];

    public function match()
    {
        return $this->belongsTo(VolleyballMatch::class, 'match_id');
    }
}
