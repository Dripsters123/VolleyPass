<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchMedia extends Model
{
    use HasFactory;

    protected $table = 'match_media';

    protected $fillable = [
        'match_id',
        'user_id',
        'type',
        'path',
        'mime',
        'caption',
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
