<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Major>
 */
class MajorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /* Example major Ids 
        * MJRU-SFTEN
        * MJRU-ICOMT
        * MJRU-COMRS
        * MJXU-BUSIN
        * MJRH-ADDSC
        * MJRU-METAL
        */

        return [
            'majorId' => fake()->regexify('MJ[RX]{1}[UH]{1}-[A-Z]{5}'),
            'name' => fake()->bs(),
        ];
    }
}
