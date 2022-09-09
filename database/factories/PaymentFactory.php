<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Period;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    private $comments = [
        'Перевод на карту',
        'Наличка',
        'Сбербанк-онлайн'
    ];

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'date_payment'=> $this->faker->date(),
            'comment' => $this->faker->randomElement($this->comments),
            'amount' =>$this->faker->randomFloat(0,600,6000),
            'period_id'=>Period::get()->random()->id,
        ];
    }
}
