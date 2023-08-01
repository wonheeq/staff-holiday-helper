<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use \DateTime;

class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $daysToAdd1 = rand(0, 70);
        $daysToAdd2 = $daysToAdd1 + rand(1, 14);

        $start = new DateTime('NOW');
        $start->modify("+{$daysToAdd1} days");

        $end = new DateTime('NOW');
        $end->modify("+{$daysToAdd2} days");

        return [
            'accountNo' => fake()->randomElement(User::pluck('id')),
            'status' => fake()->randomElement(['Y', 'N', 'U', 'P']),
            'start' => $start,
            'end' => $end,
            'processedBy' => fake()->randomElement(User::pluck('id')),
            'rejectReason' => fake()->randomElement(['Not enough leave remaining', 'A nominee declined to takeover a responsibility', 'Invalid nomination details']),
        ];
    }
}