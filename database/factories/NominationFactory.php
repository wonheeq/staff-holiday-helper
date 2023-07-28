<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Application;

class NominationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $units = ["COMP2007: Unit Coordinator", "ISEC3001: Unit Coordinator", "COMP3001: Unit Coordinator"];
        return [
            'applicationNo' => fake()->randomElement(Application::pluck('id')),
            'task' => fake()->randomElement($units),
            'nominee' => fake()->randomElement(User::pluck('id')),
            'status' => fake()->randomElement(['U', 'Y', 'N']),
        ];
    }
}