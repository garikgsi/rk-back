<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Operation;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Operation>
 */
class OperationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'date_operation'=> $this->faker->date(),
            'comment' => mb_substr($this->faker->realText(),0,rand(20,50)),
            'price' =>$this->faker->randomFloat(0,200,2000),
            'quantity' =>$this->faker->randomFloat(0,1,30),
            'amount' =>$this->faker->randomFloat(0,600,6000),
            'image' => rand(0,5)==3 ? $this->faker->imageUrl() : null,
            'check_url' => rand(0,5)==3 ? $this->faker->imageUrl() : null
        ];
    }

    /**
     * Конфигурация фабрики модели.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Operation $operation) {
            $operation->amount = $operation->price*$operation->quantity;
            $operation->period_id = $operation->plan->period_id;
            $operation->save();
        });
    }

}
