<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Period>
 */
class PeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => mb_substr($this->faker->realText(),0,rand(8,15)),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date()
        ];
    }
}
