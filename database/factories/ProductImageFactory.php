<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProductImage> */
class ProductImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'path' => 'products/test/'.fake()->uuid().'.webp',
            'variants' => null,
            'width' => 1600,
            'height' => 1200,
            'alt_text' => null,
            'is_main' => false,
            'sort_order' => 0,
        ];
    }

    public function main(): static
    {
        return $this->state(['is_main' => true]);
    }
}
