<?php

declare(strict_types=1);

namespace Tests\Feature\Migrations;

use App\Models\Invitation;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Exercises the data-migration in isolation (calling up()/down() directly)
 * rather than through `migrate`, so it can run twice against the same
 * `manager`-tagged fixture data and prove the reassignment is idempotent
 * and its rollback is precise.
 */
class ReassignShopManagerRoleMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function migration(): Migration
    {
        return require database_path('migrations/2026_09_05_000000_reassign_shop_manager_role.php');
    }

    private function makeShopInvitedManager(): array
    {
        Role::findOrCreate('manager', 'web');
        Role::findOrCreate('shop_manager', 'web');
        Role::findOrCreate('seller', 'web');

        $seller = User::factory()->create();
        $seller->assignRole('seller');

        $shop = Shop::factory()->create([
            'user_id'       => $seller->id,
            'type'          => 1,
            'delivery_time' => ['type' => 'minute', 'from' => 30, 'to' => 60],
        ]);

        $shopStaff = User::factory()->create();
        $shopStaff->assignRole('manager');

        $invitation = Invitation::query()->create([
            'shop_id'    => $shop->id,
            'user_id'    => $shopStaff->id,
            'created_by' => $seller->id,
            'role'       => 'manager',
            'status'     => Invitation::ACCEPTED,
        ]);

        return [$shopStaff, $invitation];
    }

    public function test_it_reassigns_shop_invited_manager_to_shop_manager(): void
    {
        [$shopStaff, $invitation] = $this->makeShopInvitedManager();

        $this->migration()->up();

        $shopStaff->refresh();
        $invitation->refresh();

        $this->assertTrue($shopStaff->hasRole('shop_manager'));
        $this->assertFalse($shopStaff->hasRole('manager'));
        $this->assertSame('shop_manager', $invitation->role);
    }

    public function test_it_does_not_touch_an_admin_granted_manager(): void
    {
        Role::findOrCreate('manager', 'web');
        Role::findOrCreate('shop_manager', 'web');

        // Granted directly (e.g. via Admin\UserController@updateRole) — no
        // Invitation row exists for this user at all.
        $adminManager = User::factory()->create();
        $adminManager->assignRole('manager');

        $this->migration()->up();

        $adminManager->refresh();

        $this->assertTrue($adminManager->hasRole('manager'));
        $this->assertFalse($adminManager->hasRole('shop_manager'));
    }

    public function test_running_the_migration_twice_is_idempotent(): void
    {
        [$shopStaff, $invitation] = $this->makeShopInvitedManager();

        $migration = $this->migration();
        $migration->up();
        // Second run must be a no-op: nothing left with role='manager' to find.
        $migration->up();

        $shopStaff->refresh();
        $invitation->refresh();

        $this->assertTrue($shopStaff->hasRole('shop_manager'));
        $this->assertFalse($shopStaff->hasRole('manager'));
        $this->assertSame('shop_manager', $invitation->role);
        $this->assertSame(1, $shopStaff->roles()->where('name', 'shop_manager')->count());
    }

    public function test_down_reverses_the_rename_and_reassignment(): void
    {
        [$shopStaff, $invitation] = $this->makeShopInvitedManager();

        $migration = $this->migration();
        $migration->up();
        $migration->down();

        $shopStaff->refresh();
        $invitation->refresh();

        $this->assertTrue($shopStaff->hasRole('manager'));
        $this->assertFalse($shopStaff->hasRole('shop_manager'));
        $this->assertSame('manager', $invitation->role);
        $this->assertFalse(Schema::hasTable('shop_manager_role_migration_backup'));
    }

    public function test_down_does_not_touch_shop_manager_invitations_created_after_it_ran(): void
    {
        [$shopStaff, $invitation] = $this->makeShopInvitedManager();

        $migration = $this->migration();
        $migration->up();

        // Created via the fixed invite flow after the migration ran — not
        // part of what up() touched, so down() must leave it alone.
        $seller = User::factory()->create();
        $seller->assignRole('seller');

        $newShop = Shop::factory()->create([
            'user_id'       => $seller->id,
            'type'          => 1,
            'delivery_time' => ['type' => 'minute', 'from' => 30, 'to' => 60],
        ]);

        $newStaff = User::factory()->create();
        $newStaff->assignRole('shop_manager');

        $newInvitation = Invitation::query()->create([
            'shop_id'    => $newShop->id,
            'user_id'    => $newStaff->id,
            'created_by' => $seller->id,
            'role'       => 'shop_manager',
            'status'     => Invitation::ACCEPTED,
        ]);

        $migration->down();

        $newStaff->refresh();
        $newInvitation->refresh();

        $this->assertTrue($newStaff->hasRole('shop_manager'));
        $this->assertSame('shop_manager', $newInvitation->role);
    }
}
