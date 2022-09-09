<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Plan;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    private $plans = [
        'Новый год','День рождения','8 марта','23 февраля','День учителя','Конец года','Канцелярка','Туалетная бумага','Вода',
        'Учебники','Рабочие тетради','Краски,карандаши','Альбомы','Скакалки','Картриджи','Мыло','Салфетки','Экскурсия','Тетради',
        'Коврики','Воздушные шары','Начало уч.года',
    ];

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
            'title' => $this->faker->randomElement($this->plans),
            'price' =>$price,
            'quantity' =>$quantity,
            'amount' =>$amount,
        ];
    }
}