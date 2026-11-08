<?php

namespace Database\Factories;

use App\Models\SaleUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SaleUnit> */
class SaleUnitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'sort_order' => 0,
        ];
    }
}
