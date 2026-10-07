<?php

declare(strict_types=1);

namespace Tests\Feature\Filters;

use App\Models\Category;
use App\Models\Service;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for the parent/leaf category-id mismatch: storefront
 * category icons link with the parent category's id, but Service::category_id
 * is always a leaf/child id, so an exact-match filter silently matched
 * nothing. Shop::scopeFilter / Service::scopeFilter must expand a parent id
 * to include its children.
 */
class CategoryFilterTest extends TestCase
{
    use RefreshDatabase;

    private function makeShopWithService(int $categoryId): Shop
    {
        $seller = User::factory()->create();
        $shop = Shop::factory()->create(['user_id' => $seller->id]);

        Service::query()->create([
            'category_id' => $categoryId,
            'shop_id' => $shop->id,
        ]);

        return $shop;
    }

    public function test_parent_category_id_matches_shops_whose_services_use_a_child_category(): void
    {
        $parent = Category::factory()->create(['parent_id' => 0]);
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        $matchingShop = $this->makeShopWithService($child->id);
        $unrelatedShop = $this->makeShopWithService(
            Category::factory()->create(['parent_id' => 0])->id
        );

        $result = Shop::query()->filter(['category_id' => $parent->id])->pluck('id');

        $this->assertTrue($result->contains($matchingShop->id));
        $this->assertFalse($result->contains($unrelatedShop->id));
    }

    public function test_leaf_category_id_still_matches_directly(): void
    {
        $parent = Category::factory()->create(['parent_id' => 0]);
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        $matchingShop = $this->makeShopWithService($child->id);

        $result = Shop::query()->filter(['category_id' => $child->id])->pluck('id');

        $this->assertTrue($result->contains($matchingShop->id));
    }

    public function test_category_ids_array_also_expands_parents_to_children(): void
    {
        $parent = Category::factory()->create(['parent_id' => 0]);
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        $matchingShop = $this->makeShopWithService($child->id);

        $result = Shop::query()->filter(['category_ids' => [$parent->id]])->pluck('id');

        $this->assertTrue($result->contains($matchingShop->id));
    }

    public function test_service_scope_filter_expands_parent_category_to_children(): void
    {
        $parent = Category::factory()->create(['parent_id' => 0]);
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        $service = Service::query()->create(['category_id' => $child->id]);

        $result = Service::query()->filter(['category_id' => $parent->id])->pluck('id');

        $this->assertTrue($result->contains($service->id));
    }
}
