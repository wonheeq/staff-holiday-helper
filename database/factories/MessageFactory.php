<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
define("TITLES", ["Substitution Request", "Leave Approved", "Leave Rejected"]); 

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => TITLES[rand(0, count(TITLES) - 1)],
            'content' => fake()->realText(fake()->numberBetween(10,100)),
            'acknowledged' => fake()->numberBetween(0,1),
            'receiver_id' => fake()->randomElement(User::pluck('id')),
            'sender_id' => fake()->randomElement(User::pluck('id')),
        ];
    }
}