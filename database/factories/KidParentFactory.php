<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\KidParent;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KidParent>
 */
class KidParentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'patronymic' => $this->faker->middleName(),
            'phone' => $this->faker->phoneNumber(),
            'is_admin' => $this->faker->randomDigit()==3 ? true : false,
        ];
    }

    /**
     * create male parent
     *
     * @return void
     */
    public function male()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => $this->faker->firstName('male'),
            ];
        });
    }
    /**
     * create female parent
     *
     * @return void
     */
    public function female()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => $this->faker->firstName('female'),
            ];
        });
    }


    /**
     * Конфигурация фабрики модели.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (KidParent $kidParent) {
            $users = User::withCount('kidParent')->get()->where('kid_parent_count','<',2);
            if ($users->count()>0) {
                $user = $users->random();
                $kidParent->user_id = $user->id;
                $kidParent->save();
            }
        });
    }
}
