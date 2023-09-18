<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Account;
use DateTime;
use DateTimeZone;

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
        $start->setTimezone(new DateTimeZone('Australia/Perth'));
        $start->modify("+{$daysToAdd1} days");

        $end = new DateTime('NOW');
        $end->setTimezone(new DateTimeZone('Australia/Perth'));
        $end->modify("+{$daysToAdd2} days");

        return [
            'accountNo' => fake()->randomElement(Account::pluck('accountNo')),
            'status' => fake()->randomElement(['Y', 'N', 'U', 'P']),
            'sDate' => $start,
            'eDate' => $end,
            'processedBy' => fake()->randomElement(Account::pluck('accountNo')),
            'rejectReason' => fake()->randomElement(['Not enough leave remaining', 'A nominee declined to takeover a responsibility', 'Invalid nomination details']),
        ];
    }
}