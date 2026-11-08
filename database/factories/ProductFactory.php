<?php

namespace Database\Factories;

use App\Enums\Availability;
use App\Models\Category;
use App\Models\Product;
use App\Models\SaleUnit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Prodotto '.fake()->unique()->words(2, true);

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'short_description' => fake()->sentence(),
            'description' => '<p>'.fake()->paragraph().'</p>',
            'price' => null, // « Prezzo su richiesta » par défaut
            'sale_unit_id' => null,
            'availability' => Availability::Available,
            'is_featured' => false,
            'is_active' => false,
            'sort_order' => 0,
        ];
    }

    public function active(): static
    {
        return $this->state(['is_active' => true]);
    }

    /** Prix de TEST uniquement (jamais utilisé par les seeders). */
    public function withPrice(string $price = '100.00'): static
    {
        return $this->state(fn () => [
            'price' => $price,
            'sale_unit_id' => SaleUnit::factory(),
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(['availability' => Availability::OutOfStock]);
    }
}
