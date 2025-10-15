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
        $side = $this->faker->randomElement($sides);
        $row = $this->faker->numberBetween(1, 10);
        $number = $this->faker->numberBetween(1, 20);

        return [
            'match_id'    => 1, 
            'side'        => $side,
            'row'         => $row,
            'number'      => $number,
            'seat_number' => "{$side}-{$row}-{$number}",
            'price'       => $this->faker->randomFloat(2, 5, 20),
            'ticket_id'   => null, 
        ];
    }
}
