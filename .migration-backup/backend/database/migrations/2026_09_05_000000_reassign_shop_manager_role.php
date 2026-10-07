<?php

declare(strict_types=1);

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

/**
 * Fixes the `manager` role collision: `manager` was used both for admin-panel
 * access (`role:admin|manager` route middleware) and for seller-invited shop
 * staff (`Invitation.role`), meaning a shop invite unintentionally granted
 * admin-panel access. This migration:
 *
 *  1. Introduces the `shop_manager` role (see RoleSeeder) for seller-side staff.
 *  2. Renames the `role` column value on any `invitations` row that currently
 *     says `manager` to `shop_manager` — every such row was created through
 *     the seller/admin/user invite endpoints, which only ever wrote `manager`
 *     to mean "shop manager". Genuine admin-side `manager` grants (e.g. the
 *     demo seed user, or an admin using `Admin\UserController@updateRole`)
 *     never create an `Invitation` row, so they are untouched.
 *  3. For each user tied to one of those invitations who currently holds the
 *     Spatie `manager` role, swaps it for `shop_manager`.
 *
 * A backup table records exactly which invitation/user rows were touched, so
 * `down()` can reverse precisely those rows — not any `shop_manager` invite
 * created after this migration ran.
 */
return new class extends Migration {
    private string $backupTable = 'shop_manager_role_migration_backup';

    public function up(): void
    {
        Role::findOrCreate('shop_manager', 'web');

        if (!Schema::hasTable($this->backupTable)) {
            Schema::create($this->backupTable, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invitation_id');
                $table->unsignedBigInteger('user_id');
                $table->boolean('role_was_reassigned')->default(false);
                $table->timestamps();
            });
        }

        DB::transaction(function () {
            /** @var \Illuminate\Support\Collection<int, Invitation> $invitations */
            $invitations = Invitation::where('role', 'manager')->get(['id', 'user_id']);

            if ($invitations->isEmpty()) {
                return;
            }

            foreach ($invitations as $invitation) {
                /** @var User|null $user */
                $user = User::with('roles')->find($invitation->user_id);

                $roleWasReassigned = (bool) $user?->hasRole('manager');

                DB::table($this->backupTable)->insert([
                    'invitation_id'       => $invitation->id,
                    'user_id'             => $invitation->user_id,
                    'role_was_reassigned' => $roleWasReassigned,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);

                Invitation::whereKey($invitation->id)->update(['role' => 'shop_manager']);

                if ($roleWasReassigned) {
                    $user->removeRole('manager');
                    $user->assignRole('shop_manager');
                }
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable($this->backupTable)) {
            return;
        }

        DB::transaction(function () {
            $backupRows = DB::table($this->backupTable)->get();

            foreach ($backupRows as $row) {
                Invitation::whereKey($row->invitation_id)
                    ->where('role', 'shop_manager')
                    ->update(['role' => 'manager']);

                if ($row->role_was_reassigned) {
                    /** @var User|null $user */
                    $user = User::find($row->user_id);

                    if ($user && $user->hasRole('shop_manager')) {
                        $user->removeRole('shop_manager');
                        $user->assignRole('manager');
                    }
                }
            }
        });

        Schema::dropIfExists($this->backupTable);

        // Intentionally not deleting the `shop_manager` role itself: other
        // invitations may have been created against it since this migration
        // ran, and removing the role would break those independently of
        // anything this migration is responsible for reversing.
    }
};
