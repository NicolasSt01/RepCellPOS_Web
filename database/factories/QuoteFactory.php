<?php

namespace Database\Factories;

use App\Models\Quote;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        return [
            'status' => 'pendiente',
            'subtotal' => 0,
            'tax_total' => 0,
            'total' => 0,
        ];
    }

    public function enviada(): static
    {
        return $this->state(fn() => ['status' => 'enviada']);
    }

    public function aprobada(): static
    {
        return $this->state(fn() => ['status' => 'aprobada']);
    }
}
