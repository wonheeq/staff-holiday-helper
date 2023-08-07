<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Account;
use App\Models\Role;
use App\Models\Unit;
use App\Models\Major;
use App\Models\Course;
use App\Models\School;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccountRole>
 */
class AccountRoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'accountNo' => fake()->randomElement(Account::pluck('accountNo')),
            'roleId' => fake()->randomElement(Role::pluck('roleId')),
            'unitId' => fake()->randomElement(Unit::pluck('unitId')),
            'majorId' => fake()->randomElement(Major::pluck('majorId')),
            'courseId' => fake()->randomElement(Course::pluck('courseId')),
            'schoolId' => fake()->randomElement(School::pluck('schoolId')),
        ];
    }
}
