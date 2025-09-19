<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['id', 'name', 'description', 'start_time', 'end_time'];

      public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
