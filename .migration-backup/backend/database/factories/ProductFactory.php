<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * An explicit, deterministic state for a retail catalog item attached
     * to the requested demo shop and shared platform catalog rows.
     *
     * @param array<string, mixed> $attributes
     */
    public function developmentCatalog(
        int $shopId,
        int $categoryId,
        int $brandId,
        ?int $unitId,
        array $attributes = []
    ): static {
        return $this->state(array_merge([
            'shop_id' => $shopId,
            'category_id' => $categoryId,
            'brand_id' => $brandId,
            'unit_id' => $unitId,
            'status' => Product::PUBLISHED,
            'active' => true,
            'visibility' => true,
        ], $attributes));
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'shop_id' => Shop::inRandomOrder()->first(),
            'category_id' => Category::inRandomOrder()->first(),
            'brand_id' => Brand::inRandomOrder()->first(),
            'unit_id' => Unit::inRandomOrder()->first(),
            'tax' => rand(5,10),
            'active' => true,
            'img' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
