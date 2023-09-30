<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use App\Models\Account;
use DateTime;



/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmailPreference>
 */
class EmailPreferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $time = new DateTime('NOW');
        return [
            'accountNo' => fake()->randomElement(Account::pluck('accountNo')),
            'hours' => fake()->numberBetween(1,100),
            'timeLastSent' => $time,
        ];
    }
}
