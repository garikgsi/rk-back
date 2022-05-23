<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Period;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'date_payment'=> $this->faker->date(),
            'comment' => mb_substr($this->faker->realText(),0,rand(10,30)),
            'amount' =>$this->faker->randomFloat(0,600,6000),
            'period_id'=>Period::get()->random()->id,
        ];
    }
}
