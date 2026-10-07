<?php

declare(strict_types=1);

namespace Tests\Feature\Roles;

use App\Models\Invitation;
use App\Models\Shop;
use App\Models\ShopPermission;
use App\Models\ShopRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Covers the `manager` / `shop_manager` role split: a seller-invited shop
 * manager must only reach the seller dashboard, never the admin panel, while
 * a genuinely admin-granted `manager` keeps admin access.
 */
class ShopManagerRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['user', 'seller', 'moderator', 'shop_manager', 'deliveryman', 'master', 'manager', 'admin'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        (new \Database\Seeders\ShopPermissionSeeder())->run();

        // TrustLicence/CheckParentSeller/CheckSellerShop all gate on this cache
        // key (the vendor's activation check), and CheckParentSeller/
        // CheckSellerShop additionally require 'active' => true — matching
        // the shape ProjectService::activationKeyCheck() actually returns
        // when local (see checkLocal()) — or they abort(403) regardless of
        // role. Seeding a "locally activated" response here is test-only
        // setup — it never touches shipped code — so these tests exercise
        // the role logic instead of an unrelated license lookup.
        Cache::put('rjkcvd.ewoidfh', (object) ['local' => true, 'active' => true]);
    }

    public function test_seller_invited_shop_manager_cannot_access_admin_routes(): void
    {
        $staff = $this->makeAcceptedShopStaff('shop_manager');

        $response = $this->actingAs($staff, 'sanctum')
            ->getJson('/api/v1/dashboard/admin/users/paginate');

        $response->assertStatus(403);
    }

    public function test_admin_granted_manager_can_access_admin_routes(): void
    {
        $adminManager = User::factory()->create();
        $adminManager->assignRole('manager');

        $response = $this->actingAs($adminManager, 'sanctum')
            ->getJson('/api/v1/dashboard/admin/users/paginate');

        $response->assertStatus(200);
    }

    public function test_shop_manager_can_access_seller_routes(): void
    {
        $staff = $this->makeAcceptedShopStaff('shop_manager');

        $response = $this->actingAs($staff, 'sanctum')
            ->getJson('/api/v1/dashboard/seller/payouts');

        $response->assertStatus(200);
    }

    private function makeAcceptedShopStaff(string $role): User
    {
        $seller = User::factory()->create();
        $seller->assignRole('seller');

        $shop = Shop::factory()->create([
            'user_id'       => $seller->id,
            'type'          => 1,
            'delivery_time' => ['type' => 'minute', 'from' => 30, 'to' => 60],
        ]);

        $staff = User::factory()->create();
        $staff->assignRole($role);

        // The seller-route check is two layers: CheckSellerShop (role-based,
        // what this test targets) and, on top of it, `shop.permission:*`
        // (fine-grained, per-shop_role — see CheckShopPermission). An accepted
        // invitation with no shop_role_id grants no shop permissions at all,
        // so give this staff member a role that can reach the routes below.
        $shopRole = ShopRole::query()->create(['shop_id' => $shop->id, 'name' => 'Test Staff']);
        $shopRole->permissions()->sync(ShopPermission::query()->where('key', 'payments.view')->pluck('id'));

        Invitation::query()->create([
            'shop_id'      => $shop->id,
            'user_id'      => $staff->id,
            'created_by'   => $seller->id,
            'role'         => $role,
            'shop_role_id' => $shopRole->id,
            'status'       => Invitation::ACCEPTED,
        ]);

        return $staff;
    }
}
