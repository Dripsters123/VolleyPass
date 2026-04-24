<?php

namespace Database\Factories;

use App\Models\Seat;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeatFactory extends Factory
{
    protected $model = Seat::class;

    public function definition()
    {
        $sides = ['top', 'bottom', 'left', 'right'];
        $side = fake('en_US')->randomElement($sides);
        $row = fake('en_US')->numberBetween(1, 10);
        $number = fake('en_US')->numberBetween(1, 20);

        return [
            'match_id'    => 1, 
            'side'        => $side,
            'row'         => $row,
            'number'      => $number,
            'seat_number' => "{$side}-{$row}-{$number}",
            'price'       => fake('en_US')->randomFloat(2, 5, 20),
            'ticket_id'   => null, 
        ];
    }
}
