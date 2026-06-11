<?php

namespace Database\Factories;

use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkOrderFactory extends Factory
{
    protected $model = WorkOrder::class;

    public function definition(): array
    {
        return [
            'client_id' => \App\Models\Client::factory(),
            'user_id' => \App\Models\User::factory(),
            'device_brand' => fake()->randomElement(['Apple', 'Samsung', 'Xiaomi', 'Motorola']),
            'device_model' => fake()->bothify('Model ??###'),
            'device_serial' => fake()->bothify('SN####??##'),
            'device_imei' => fake()->numerify('##############'),
            'problem_description' => fake()->sentence(8),
            'status' => 'en_espera',
            'priority' => 'media',
            'work_order_number' => 'WO-' . fake()->unique()->numerify('#####'),
        ];
    }

    public function diagnosticada(): static
    {
        return $this->state(fn() => ['status' => 'diagnosticada']);
    }

    public function cotizacionAprobada(): static
    {
        return $this->state(fn() => ['status' => 'cotizacion_aprobada']);
    }

    public function reparada(): static
    {
        return $this->state(fn() => ['status' => 'reparada']);
    }
}
