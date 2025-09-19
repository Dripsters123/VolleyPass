<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VolleyballMatchStatistic extends Model
{
     protected $fillable = ['match_id','type','period','category','home_team','away_team'];

    public function match()
    {
        return $this->belongsTo(VolleyballMatch::class, 'match_id');
    }
}
