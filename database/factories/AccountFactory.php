<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;



/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'accountNo' => fake()->unique()->bothify('######?'),
            'accountType' => fake()->randomElement(['staff', 'lmanager', 'sysadmin']),
            'lName' => fake()->lastName(),
            'fName' => fake()->firstName(),
            'password' => Hash::make(fake()->regexify('[A-Za-z0-9#@$%^&*]{10,15}')),
            'superiorNo' => fake()->randomElement(['0000000']),
            'schoolId' => fake()->numberBetween(101, 114),  // 14 schools
            'isTemporaryManager' => false,
        ];
    }
}
