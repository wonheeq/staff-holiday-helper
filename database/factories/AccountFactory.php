<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

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
            'password' => fake()->regexify('[A-Za-z0-9#@$%^&*]{10,15}'),
            'superiorNo' => fake()->randomElement(['112237t', '123456a', '441817e', '877873p']),
            'schoolId' => fake()->numberBetween(101, 114),  // 14 schools
        ];
    }
}
