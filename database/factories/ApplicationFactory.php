<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTime();

        return [
            'accountNo' => fake()->randomElement(User::pluck('id')),
            'status' => fake()->randomElement(['Y', 'N', 'U', 'P']),
            'start' => $start,
            'end' => $start->modify('+4 day'),
        ];
    }
}