<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Account;
use App\Models\Application;
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
            'applicationNo' => fake()->randomElement(Application::pluck("applicationNo")),
            'receiverNo' => fake()->randomElement(Account::pluck("accountNo")),
            'senderNo' => fake()->randomElement(Account::pluck("accountNo")),
            'subject' => fake()->randomElement(TITLES),
            'content' => fake()->realText(fake()->numberBetween(10,100)),
            'acknowledged' => fake()->numberBetween(0,1) == 1,
        ];
    }
}