<?php

declare(strict_types=1);

namespace Tests\Feature\Migrations;

use App\Models\Invitation;
use App\Models\Shop;
use App\Models\ShopPermission;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Confirms the item-5 migration: an existing shop_manager holder gets a
 * shop_role_id pointing at an auto-created "Manager" role with every
 * permission, while their underlying 'shop_manager' Spatie role — which
 * still gates seller-dashboard entry at the route level — is left
 * untouched. Nobody loses access.
 */
class MigrateShopManagerHoldersTest extends TestCase
{
    use RefreshDatabase;

    private function migration(): Migration
    {
        return require database_path('migrations/2026_09_05_070000_migrate_shop_manager_holders_to_shop_roles.php');
    }

    public function test_existing_shop_manager_holder_gets_a_full_permission_role_and_keeps_access(): void
    {
        Role::findOrCreate('shop_manager', 'web');
        (new \Database\Seeders\ShopPermissionSeeder())->run();

        $seller = User::factory()->create();
        $shop = Shop::factory()->create([
            'user_id'       => $seller->id,
            'type'          => 1,
            'delivery_time' => ['type' => 'minute', 'from' => 30, 'to' => 60],
        ]);

        $staff = User::factory()->create();
        $staff->assignRole('shop_manager');

        $invitation = Invitation::query()->create([
            'shop_id'    => $shop->id,
            'user_id'    => $staff->id,
            'created_by' => $seller->id,
            'role'       => 'shop_manager',
            'status'     => Invitation::ACCEPTED,
        ]);

        $this->migration()->up();

        $staff->refresh();
        $invitation->refresh();

        // Nobody loses access: the Spatie role that gates dashboard entry
        // is completely untouched by this migration.
        $this->assertTrue($staff->hasRole('shop_manager'));

        $this->assertNotNull($invitation->shop_role_id);

        $role = $invitation->shopRole()->with('permissions')->first();
        $this->assertSame('Manager', $role->name);
        $this->assertSame(ShopPermission::count(), $role->permissions->count());
    }

    public function test_does_not_touch_a_shop_manager_invitation_without_the_underlying_role(): void
    {
        Role::findOrCreate('shop_manager', 'web');
        (new \Database\Seeders\ShopPermissionSeeder())->run();

        $seller = User::factory()->create();
        $shop = Shop::factory()->create([
            'user_id'       => $seller->id,
            'type'          => 1,
            'delivery_time' => ['type' => 'minute', 'from' => 30, 'to' => 60],
        ]);

        // Invitation row exists but the user's Spatie role was since
        // revoked some other way — should not get a shop_role either.
        $staff = User::factory()->create();

        $invitation = Invitation::query()->create([
            'shop_id'    => $shop->id,
            'user_id'    => $staff->id,
            'created_by' => $seller->id,
            'role'       => 'shop_manager',
            'status'     => Invitation::ACCEPTED,
        ]);

        $this->migration()->up();

        $invitation->refresh();
        $this->assertNull($invitation->shop_role_id);
    }

    public function test_running_the_migration_twice_is_idempotent(): void
    {
        Role::findOrCreate('shop_manager', 'web');
        (new \Database\Seeders\ShopPermissionSeeder())->run();

        $seller = User::factory()->create();
        $shop = Shop::factory()->create([
            'user_id'       => $seller->id,
            'type'          => 1,
            'delivery_time' => ['type' => 'minute', 'from' => 30, 'to' => 60],
        ]);

        $staff = User::factory()->create();
        $staff->assignRole('shop_manager');

        Invitation::query()->create([
            'shop_id'    => $shop->id,
            'user_id'    => $staff->id,
            'created_by' => $seller->id,
            'role'       => 'shop_manager',
            'status'     => Invitation::ACCEPTED,
        ]);

        $migration = $this->migration();
        $migration->up();
        $migration->up();

        $this->assertSame(1, \App\Models\ShopRole::where('shop_id', $shop->id)->count());
    }
}
