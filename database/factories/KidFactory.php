<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kid>
 */
class KidFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $sexArray = ['male', 'female'];
        $sex = $this->faker->randomElement($sexArray);
        $year = date('Y');
        return [
            'birthday' => $this->faker->dateTimeBetween('2014-01-01 00:00:00', '2014-12-31 00:00:00'),
            'name' => $this->faker->firstName($sex),
            'last_name' => $this->faker->lastName($sex),
            'patronymic' => $this->faker->middleName($sex),
        ];
    }
}