<?php

declare(strict_types=1);

namespace Tests\Feature\ShopRoles;

use App\Models\Invitation;
use App\Models\Shop;
use App\Models\ShopRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The seller-facing staff list must show which shop_role each invited
 * staff member holds — InviteResource previously never loaded or exposed
 * the shopRole relation at all.
 */
class StaffInvitationListTest extends TestCase
{
    use RefreshDatabase;

    public function test_paginated_invites_expose_the_assigned_shop_role(): void
    {
        Role::findOrCreate('seller', 'web');

        $seller = User::factory()->create();
        $seller->assignRole('seller');

        $shop = Shop::factory()->create([
            'user_id'       => $seller->id,
            'type'          => 1,
            'delivery_time' => ['type' => 'minute', 'from' => 30, 'to' => 60],
        ]);

        $role  = ShopRole::query()->create(['shop_id' => $shop->id, 'name' => 'Front Desk']);
        $staff = User::factory()->create();

        Invitation::query()->create([
            'shop_id'      => $shop->id,
            'user_id'      => $staff->id,
            'created_by'   => $seller->id,
            'role'         => 'shop_manager',
            'shop_role_id' => $role->id,
            'status'       => Invitation::ACCEPTED,
        ]);

        $response = $this->actingAs($seller, 'sanctum')
            ->getJson('/api/v1/dashboard/seller/shops/invites/paginate');

        $response->assertStatus(200);
        $this->assertSame($role->id, $response->json('data.0.shop_role.id'));
        $this->assertSame('Front Desk', $response->json('data.0.shop_role.name'));
    }
}
