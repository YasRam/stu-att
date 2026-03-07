<?php

namespace Database\Factories;

use App\Models\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stage>
 */
class StageFactory extends Factory
{
    protected $model = Stage::class;

    public function definition(): array
    {
        $order = $this->faker->numberBetween(1, 20);
        $nameAr = $this->faker->randomElement(['1 ب', '1 ع', '2 ب', '2 ع', '3 ب', '3 ع', '4 أ', '4 ب']);
        $nameEn = 'S' . $order;

        return [
            'name_ar' => $nameAr,
            'name_en' => $nameEn,
            'order_index' => $order,
        ];
    }
}
