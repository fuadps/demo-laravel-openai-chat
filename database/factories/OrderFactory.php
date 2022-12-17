<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition()
    {
        $foods = [
            'Nasi Lemak',
            'Nasi Kerabu',
            'Donut',
            'Karipap',
            'Kuih Lapis',
            'Kuih Kapit',
            'Kuih Lompang',
            'Kuih Bakul',
            'Kuih Bangkit',
            'Kuih Seri Muka',
        ];

        return [
            'item' => fake()->randomElement($foods),
            'price' => fake()->randomFloat(2, 100, 1000),
            'quantity' => fake()->numberBetween(1, 100),
        ];
    }
}
