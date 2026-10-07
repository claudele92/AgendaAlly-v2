<?php

declare(strict_types=1);

namespace Tests\Feature\PaymentGateways;

use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Payment;
use App\Models\Region;
use App\Models\Shop;
use App\Models\User;
use App\Models\UserCart;
use App\Services\PaymentService\OrangeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Orange Money/MTN Mobile Money settle directly into one shop's own
 * merchant account — a cart spanning more than one shop has no single
 * account to pay into. This must be rejected server-side (not just hidden
 * in the UI), and the payment-method listing endpoint must not offer
 * Orange/MTN for such a cart in the first place.
 */
class MultiShopCartTest extends TestCase
{
    use RefreshDatabase;

    private function makeCart(array $shopIds): Cart
    {
        $owner  = User::factory()->create();
        $region = Region::query()->create(['active' => true]);

        $cart = Cart::query()->create([
            'owner_id'         => $owner->id,
            'region_id'        => $region->id,
            'total_price'      => 100,
            'rate_total_price' => 100,
            'status'           => true,
            'rate'             => 1,
        ]);

        $userCart = UserCart::query()->create([
            'cart_id' => $cart->id,
            'user_id' => $owner->id,
            'status'  => true,
        ]);

        foreach ($shopIds as $shopId) {
            CartDetail::query()->create([
                'shop_id'       => $shopId,
                'user_cart_id'  => $userCart->id,
            ]);
        }

        return $cart;
    }

    private function makeShop(): Shop
    {
        return Shop::factory()->create([
            'user_id'       => User::factory()->create()->id,
            'type'          => 1,
            'delivery_time' => ['type' => 'minute', 'from' => 30, 'to' => 60],
        ]);
    }

    public function test_a_multi_shop_cart_is_rejected_when_resolving_gateway_credentials(): void
    {
        $shopA = $this->makeShop();
        $shopB = $this->makeShop();
        $cart  = $this->makeCart([$shopA->id, $shopB->id]);

        $this->expectExceptionMessage('This payment method does not support a cart with items from more than one shop');

        (new OrangeService())->resolveGatewayShopId(Cart::class, $cart->id);
    }

    public function test_a_single_shop_cart_resolves_to_that_shop(): void
    {
        $shop = $this->makeShop();
        $cart = $this->makeCart([$shop->id]);

        $resolved = (new OrangeService())->resolveGatewayShopId(Cart::class, $cart->id);

        $this->assertSame($shop->id, $resolved);
    }

    public function test_payment_listing_excludes_orange_and_mtn_for_a_multi_shop_cart(): void
    {
        Payment::query()->create(['tag' => Payment::TAG_ORANGE, 'active' => true, 'input' => 15]);
        Payment::query()->create(['tag' => Payment::TAG_MTN, 'active' => true, 'input' => 15]);
        Payment::query()->create(['tag' => Payment::TAG_CASH, 'active' => true, 'input' => 15]);

        $shopA = $this->makeShop();
        $shopB = $this->makeShop();
        $cart  = $this->makeCart([$shopA->id, $shopB->id]);

        $response = $this->getJson("/api/v1/rest/payments?cart_id={$cart->id}");

        $tags = collect($response->json('data'))->pluck('tag');

        $this->assertFalse($tags->contains(Payment::TAG_ORANGE));
        $this->assertFalse($tags->contains(Payment::TAG_MTN));
        $this->assertTrue($tags->contains(Payment::TAG_CASH));
    }

    public function test_payment_listing_includes_orange_and_mtn_for_a_single_shop_cart(): void
    {
        Payment::query()->create(['tag' => Payment::TAG_ORANGE, 'active' => true, 'input' => 15]);

        $shop = $this->makeShop();
        $cart = $this->makeCart([$shop->id]);

        $response = $this->getJson("/api/v1/rest/payments?cart_id={$cart->id}");

        $tags = collect($response->json('data'))->pluck('tag');

        $this->assertTrue($tags->contains(Payment::TAG_ORANGE));
    }
}
