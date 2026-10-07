<?php

declare(strict_types=1);

namespace Tests\Feature\ShopRoles;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The invite-by-user_id flow (Invitation\SellerRequest) has no free-text
 * email/phone field at all — the seller must resolve an exact email/phone
 * to an existing user first. Covers that lookup: exact match only, masked
 * response, and sellers/admins excluded (matches InviteService::sellerCreate's
 * own guard, so a seller never sees a "found" result they couldn't actually
 * invite).
 */
class StaffUserSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('seller', 'web');
        Role::findOrCreate('admin', 'web');
    }

    private function makeSeller(): User
    {
        $seller = User::factory()->create();
        $seller->assignRole('seller');

        Shop::factory()->create([
            'user_id'       => $seller->id,
            'type'          => 1,
            'delivery_time' => ['type' => 'minute', 'from' => 30, 'to' => 60],
        ]);

        return $seller;
    }

    public function test_exact_email_match_returns_masked_user(): void
    {
        $seller = $this->makeSeller();
        $target = User::factory()->create(['email' => 'jane@example.com', 'firstname' => 'Jane', 'lastname' => 'Doe']);

        $response = $this->actingAs($seller, 'sanctum')
            ->getJson('/api/v1/dashboard/seller/shop/invitation/search-user?query=jane@example.com');

        $response->assertStatus(200);
        $this->assertSame($target->id, $response->json('data.id'));
        $this->assertSame('Jane Doe', $response->json('data.name'));
        $this->assertSame('j***@example.com', $response->json('data.email'));
    }

    public function test_exact_phone_match_returns_masked_user(): void
    {
        $seller = $this->makeSeller();
        $target = User::factory()->create(['phone' => '+12345554567']);

        $response = $this->actingAs($seller, 'sanctum')
            ->getJson('/api/v1/dashboard/seller/shop/invitation/search-user?query=' . urlencode('+12345554567'));

        $response->assertStatus(200);
        $this->assertSame($target->id, $response->json('data.id'));
        $this->assertSame('+1******4567', $response->json('data.phone'));
    }

    public function test_no_match_returns_404(): void
    {
        $seller = $this->makeSeller();

        $response = $this->actingAs($seller, 'sanctum')
            ->getJson('/api/v1/dashboard/seller/shop/invitation/search-user?query=nobody@example.com');

        $response->assertStatus(404);
    }

    public function test_partial_match_is_not_found(): void
    {
        $seller = $this->makeSeller();
        User::factory()->create(['email' => 'jane@example.com']);

        $response = $this->actingAs($seller, 'sanctum')
            ->getJson('/api/v1/dashboard/seller/shop/invitation/search-user?query=jane');

        $response->assertStatus(404);
    }

    public function test_a_seller_or_admin_user_is_never_returned(): void
    {
        $seller = $this->makeSeller();

        $otherSeller = User::factory()->create(['email' => 'other-seller@example.com']);
        $otherSeller->assignRole('seller');

        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $admin->assignRole('admin');

        $this->actingAs($seller, 'sanctum')
            ->getJson('/api/v1/dashboard/seller/shop/invitation/search-user?query=other-seller@example.com')
            ->assertStatus(404);

        $this->actingAs($seller, 'sanctum')
            ->getJson('/api/v1/dashboard/seller/shop/invitation/search-user?query=admin@example.com')
            ->assertStatus(404);
    }
}
