<?php

declare(strict_types=1);

use App\Models\Invitation;
use App\Models\ShopPermission;
use App\Models\ShopRole;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * For every shop that currently has a staff member holding the fixed
 * 'shop_manager' role (from the previous role-collision fix), auto-creates
 * a "Manager" shop_role for that specific shop with every permission in the
 * catalog, and points that invitation at it. The 'shop_manager' Spatie role
 * itself is left untouched on the user — it still gates seller-dashboard
 * entry at the route level; shop_role_id only adds the fine-grained layer.
 * Nobody loses dashboard access: this only ever adds a shop_role_id, never
 * removes the underlying role.
 *
 * The broad "grant every permission" choice is deliberate and flagged for
 * review — it's the only way to guarantee zero regression for someone who
 * previously had full seller-dashboard access under the old single
 * shop_manager role, including shop_settings.manage and staff.roles.manage.
 * Sellers can narrow the auto-created "Manager" role afterward if they want.
 */
return new class extends Migration {
    private string $backupTable = 'shop_manager_holders_migration_backup';

    public function up(): void
    {
        if (!Schema::hasTable($this->backupTable)) {
            Schema::create($this->backupTable, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invitation_id');
                $table->unsignedBigInteger('shop_role_id');
                $table->boolean('role_was_created');
                $table->timestamps();
            });
        }

        DB::transaction(function () {
            $allPermissionIds = ShopPermission::query()->pluck('id');

            $invitations = Invitation::query()
                ->where('role', 'shop_manager')
                ->whereNull('shop_role_id')
                ->get(['id', 'shop_id', 'user_id']);

            $createdRoleForShop = [];

            foreach ($invitations as $invitation) {
                /** @var User|null $user */
                $user = User::find($invitation->user_id);

                if (!$user?->hasRole('shop_manager')) {
                    continue;
                }

                if (!isset($createdRoleForShop[$invitation->shop_id])) {
                    /** @var ShopRole $role */
                    $role = ShopRole::query()->create([
                        'shop_id' => $invitation->shop_id,
                        'name'    => 'Manager',
                    ]);

                    $role->permissions()->sync($allPermissionIds);

                    $createdRoleForShop[$invitation->shop_id] = $role->id;

                    DB::table($this->backupTable)->insert([
                        'invitation_id'    => $invitation->id,
                        'shop_role_id'     => $role->id,
                        'role_was_created' => true,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                } else {
                    DB::table($this->backupTable)->insert([
                        'invitation_id'    => $invitation->id,
                        'shop_role_id'     => $createdRoleForShop[$invitation->shop_id],
                        'role_was_created' => false,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }

                Invitation::whereKey($invitation->id)->update([
                    'shop_role_id' => $createdRoleForShop[$invitation->shop_id],
                ]);
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable($this->backupTable)) {
            return;
        }

        DB::transaction(function () {
            $rows = DB::table($this->backupTable)->get();

            foreach ($rows as $row) {
                Invitation::whereKey($row->invitation_id)
                    ->where('shop_role_id', $row->shop_role_id)
                    ->update(['shop_role_id' => null]);
            }

            $createdRoleIds = $rows->where('role_was_created', true)->pluck('shop_role_id');

            DB::table('shop_role_permissions')->whereIn('shop_role_id', $createdRoleIds)->delete();
            DB::table('shop_roles')->whereIn('id', $createdRoleIds)->delete();
        });

        Schema::dropIfExists($this->backupTable);
    }
};
