<?php

namespace Database\Factories;

use App\Models\QuoteItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteItemFactory extends Factory
{
    protected $model = QuoteItem::class;

    public function definition(): array
    {
        $unitPrice = fake()->randomFloat(2, 50, 5000);
        $quantity = fake()->numberBetween(1, 5);

        return [
            'type' => 'servicio',
            'description' => fake()->sentence(3),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax_percentage' => 16,
            'subtotal' => $quantity * $unitPrice,
        ];
    }

    public function producto(): static
    {
        return $this->state(fn() => [
            'type' => 'producto',
        ]);
    }
}
