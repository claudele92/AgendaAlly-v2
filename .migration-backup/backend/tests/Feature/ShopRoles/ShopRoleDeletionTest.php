<?php

declare(strict_types=1);

namespace Tests\Feature\ShopRoles;

use App\Models\Invitation;
use App\Models\Shop;
use App\Models\ShopPermission;
use App\Models\ShopRole;
use App\Models\User;
use App\Services\ShopRoleService\ShopRoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Deleting a shop_role still referenced by a staff invitation is blocked
 * explicitly (not reassigned/nulled silently) — matches the
 * invitations.shop_role_id restrictOnDelete FK.
 */
class ShopRoleDeletionTest extends TestCase
{
    use RefreshDatabase;

    private function makeShop(): Shop
    {
        $seller = User::factory()->create();

        return Shop::factory()->create([
            'user_id'       => $seller->id,
            'type'          => 1,
            'delivery_time' => ['type' => 'minute', 'from' => 30, 'to' => 60],
        ]);
    }

    public function test_deleting_a_role_still_assigned_to_staff_is_blocked(): void
    {
        (new \Database\Seeders\ShopPermissionSeeder())->run();

        $shop = $this->makeShop();
        $role = ShopRole::query()->create(['shop_id' => $shop->id, 'name' => 'Front Desk']);
        $role->permissions()->sync(ShopPermission::query()->pluck('id')->take(2));

        $staff = User::factory()->create();
        Invitation::query()->create([
            'shop_id'      => $shop->id,
            'user_id'      => $staff->id,
            'created_by'   => $shop->user_id,
            'role'         => 'shop_manager',
            'shop_role_id' => $role->id,
            'status'       => Invitation::ACCEPTED,
        ]);

        $result = (new ShopRoleService())->deleteRole($role);

        $this->assertFalse($result['status']);
        $this->assertNotNull(ShopRole::find($role->id), 'role must still exist');
    }

    public function test_deleting_an_unused_role_succeeds(): void
    {
        $shop = $this->makeShop();
        $role = ShopRole::query()->create(['shop_id' => $shop->id, 'name' => 'Unused Role']);

        $result = (new ShopRoleService())->deleteRole($role);

        $this->assertTrue($result['status']);
        $this->assertNull(ShopRole::find($role->id));
    }
}
