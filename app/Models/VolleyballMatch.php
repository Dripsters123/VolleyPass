<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VolleyballMatch extends Model
{
    use HasFactory;

     protected $fillable = [
        'id','home_team_name','away_team_name','status_type',
        'start_time','end_time','home_score','away_score',
        'arena','tournament','season','round','league'
    ];

    protected $casts = [
        'arena' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];
    
    public function statistics()
    {
        return $this->hasMany(VolleyballMatchStatistic::class, 'match_id');
    }
}
