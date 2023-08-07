<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /* Example course Ids 
        * B-COMP
        * MC-ISYS
        * GC-DGFUT
        * GD-COMP
        * MC-ACTFNS
        * DR-BUSADM
        */

        return [
            'courseId' => fake()->regexify('[A-Z]{1,2}-[A-Z]{4,7}'),
            'name' => fake()->catchPhrase(),
        ];
    }
}
