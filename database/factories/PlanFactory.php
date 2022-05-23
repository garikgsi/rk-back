<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Plan;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => mb_substr($this->faker->realText(),0,rand(20,50)),
            'price' =>$this->faker->randomFloat(0,200,2000),
            'quantity' =>$this->faker->randomFloat(0,1,30),
            'amount' =>$this->faker->randomFloat(0,600,6000),
        ];
    }

    /**
     * Конфигурация фабрики модели.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Plan $plan) {
            $plan->amount = $plan->price*$plan->quantity;
            $plan->save();
        });
    }
}
