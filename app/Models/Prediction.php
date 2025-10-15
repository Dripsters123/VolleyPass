<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prediction extends Model
{
    protected $fillable = ['user_id','match_id','prediction','staked_coins','status','reward'];
    public function user(){ return $this->belongsTo(User::class); }
    public function match(){ return $this->belongsTo(VolleyballMatch::class,'match_id'); }
}
