<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'message' => $this->faker->words(3,true),
            'is_translit' => $this->faker->boolean(50),
            'number' => $this->faker->phoneNumber(),
            'cost' => $this->faker->randomFloat(2,1,5),
        ];
    }
}
