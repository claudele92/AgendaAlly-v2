<?php

declare(strict_types=1);

namespace Tests\Feature\ShopRoles;

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
 * Covers the bespoke shop_roles permission system: a staff member whose
 * role only grants bookings.* permissions can reach a bookings endpoint but
 * not a payments one, while the shop owner always has every permission
 * regardless of any shop_role — see User::hasShopPermission().
 */
class ShopPermissionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['seller', 'shop_manager'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        (new \Database\Seeders\ShopPermissionSeeder())->run();

        // Bypass the vendor license-activation gate (CheckParentSeller /
        // CheckSellerShop / TrustLicence) — test-only setup, never touches
        // shipped code. See ShopManagerRoleAccessTest for the same pattern.
        Cache::put('rjkcvd.ewoidfh', (object) ['local' => true, 'active' => true]);
    }

    private function makeShopWithStaff(array $permissionKeys): array
    {
        $seller = User::factory()->create();
        $seller->assignRole('seller');

        $shop = Shop::factory()->create([
            'user_id'       => $seller->id,
            'type'          => 1,
            'delivery_time' => ['type' => 'minute', 'from' => 30, 'to' => 60],
        ]);

        $role = ShopRole::query()->create(['shop_id' => $shop->id, 'name' => 'Front Desk']);
        $permissionIds = ShopPermission::query()->whereIn('key', $permissionKeys)->pluck('id');
        $role->permissions()->sync($permissionIds);

        $staff = User::factory()->create();
        $staff->assignRole('shop_manager');

        Invitation::query()->create([
            'shop_id'      => $shop->id,
            'user_id'      => $staff->id,
            'created_by'   => $seller->id,
            'role'         => 'shop_manager',
            'shop_role_id' => $role->id,
            'status'       => Invitation::ACCEPTED,
        ]);

        return [$seller, $shop, $staff, $role];
    }

    public function test_staff_with_bookings_only_role_cannot_hit_payments_endpoint(): void
    {
        [, , $staff] = $this->makeShopWithStaff(['bookings.view', 'bookings.manage']);

        $response = $this->actingAs($staff, 'sanctum')->getJson('/api/v1/dashboard/seller/payouts');

        $response->assertStatus(403);
    }

    public function test_staff_with_bookings_only_role_can_hit_bookings_endpoint(): void
    {
        [, , $staff] = $this->makeShopWithStaff(['bookings.view']);

        $response = $this->actingAs($staff, 'sanctum')->getJson('/api/v1/dashboard/seller/bookings');

        $response->assertStatus(200);
    }

    public function test_owner_has_every_permission_with_no_shop_role_at_all(): void
    {
        $seller = User::factory()->create();
        $seller->assignRole('seller');

        $shop = Shop::factory()->create([
            'user_id'       => $seller->id,
            'type'          => 1,
            'delivery_time' => ['type' => 'minute', 'from' => 30, 'to' => 60],
        ]);

        $this->assertTrue($seller->hasShopPermission($shop->id, 'payments.gateways.manage'));
        $this->assertTrue($seller->hasShopPermission($shop->id, 'staff.roles.manage'));
        $this->assertTrue($seller->hasShopPermission($shop->id, 'anything.not.in.the.catalog'));
    }
}
