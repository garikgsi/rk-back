<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Operation;
use Carbon\Carbon;

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
        $price = $this->faker->randomFloat(2,200,2000);
        $quantity = $this->faker->randomFloat(0,1,30);
        $amount = round($price*$quantity,2);

        return [
            'date_operation'=> $this->faker->date(),
            'comment' => mb_substr($this->faker->realText(),0,rand(20,50)),
            'price' =>$price,
            'quantity' =>$quantity,
            'amount' =>$amount,
            // 'image' => rand(0,5)==3 ? $this->faker->imageUrl() : null,
            'check_url' => rand(0,5)<3 ? $this->faker->imageUrl() : null
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
            // operations dates
            $planStartDate = $operation->plan->period->start_date;
            $planEndDate = $operation->plan->period->end_date;
            $now = Carbon::now();
            // prices
            $price = round(rand(0,100) > 90 ? $operation->plan->price : $operation->plan->price*(rand(100, 120))/100,2);
            $quantity = $this->faker->randomFloat(0,1,ceil($operation->quantity/2));
            $amount = $price*$quantity;

            $operation->period_id = $operation->plan->period_id;
            $operation->date_operation = $this->faker->dateTimeBetween($planStartDate.' 00:00:00', ($planEndDate > $now ? $now->format("Y-m-d") : $planEndDate).' 00:00:00');
            $operation->price = $price;
            $operation->quantity = $quantity;
            $operation->amount = $amount;
            // titles
            $operation->comment = $operation->plan->title;

            $operation->save();
        });
    }

}
