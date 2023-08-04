<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Account;
use App\Models\Application;
use App\Models\AccountRole;

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
            'applicationNo' => fake()->randomElement(Application::pluck('applicationNo')),
            'accountRoleId' => fake()->randomElement(AccountRole::pluck('accountRoleId')),
            'nomineeNo' => fake()->randomElement(Account::pluck('accountNo')),
            'status' => fake()->randomElement(['U', 'Y', 'N']),
        ];
    }
}