<?php

namespace Database\Factories;

use App\Models\Seat;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeatFactory extends Factory
{
    protected $model = Seat::class;

    public function definition()
    {
        $faker = \Faker\Factory::create('en_US');
        $sides = ['top', 'bottom', 'left', 'right'];
        $side = $faker->randomElement($sides);
        $row = $faker->numberBetween(1, 10);
        $number = $faker->numberBetween(1, 20);

        return [
            'match_id'    => 1, 
            'side'        => $side,
            'row'         => $row,
            'number'      => $number,
            'seat_number' => "{$side}-{$row}-{$number}",
            'price'       => $faker->randomFloat(2, 5, 20),
            'ticket_id'   => null, 
        ];
    }
}
