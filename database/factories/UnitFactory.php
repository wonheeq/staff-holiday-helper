<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unit>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'unitId' => fake()->regexify('[A-Z]{4}[1-4]{1}00[1-4]{1}'),
            'name' => fake()->catchPhrase(),
        ];
    }
}
