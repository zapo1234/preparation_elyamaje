<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class HistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            "order_id" => 65000,
            "user_id" =>  User::all()->random()->id, 
            "status" => "finished" ,
            "created_at" => $this->faker->dateTimeThisYear()
        ];
    }
}