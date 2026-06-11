<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('PROD-####'),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'type' => fake()->randomElement(['producto', 'servicio']),
            'stock' => fake()->numberBetween(0, 100),
            'reserved_stock' => fake()->numberBetween(0, 10),
            'min_stock' => fake()->numberBetween(1, 10),
            'purchase_price' => fake()->randomFloat(2, 10, 500),
            'sale_price' => fake()->randomFloat(2, 50, 1500),
            'has_tax' => true,
            'tax_percentage' => 16,
            'is_active' => true,
        ];
    }
}
