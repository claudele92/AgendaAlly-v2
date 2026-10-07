<?php

declare(strict_types=1);

namespace Tests\Feature\Checkout;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\Region;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the core of the country-based currency/gateway routing: a shop's
 * SERVICE (booking) and PRODUCT (e-commerce) arms resolve independently and
 * can sit in different countries; cash/wallet are always available
 * regardless of country; and a country's active gateway list only reflects
 * gateways explicitly enabled for it.
 */
class ShopCheckoutCountryTest extends TestCase
{
    use RefreshDatabase;

    private function makeCountry(string $code, ?Currency $currency = null): Country
    {
        return Country::query()->create([
            'active'      => true,
            'code'        => $code,
            'currency_id' => $currency?->id,
        ]);
    }

    private function makeShop(): Shop
    {
        $seller = User::factory()->create();

        return Shop::factory()->create([
            'user_id'       => $seller->id,
            'type'          => 1,
            'delivery_time' => ['type' => 'minute', 'from' => 30, 'to' => 60],
        ]);
    }

    public function test_service_and_product_locations_resolve_independently(): void
    {
        $region = Region::query()->create(['active' => true]);

        $currencyA = Currency::factory()->create(['title' => 'A', 'default' => true]);
        $currencyB = Currency::factory()->create(['title' => 'B', 'default' => false]);

        $countryService = $this->makeCountry('SV', $currencyA);
        $countryProduct = $this->makeCountry('PR', $currencyB);

        $shop = $this->makeShop();

        ShopLocation::query()->create([
            'shop_id'    => $shop->id,
            'region_id'  => $region->id,
            'country_id' => $countryService->id,
            'type'       => ShopLocation::SERVICE,
        ]);

        ShopLocation::query()->create([
            'shop_id'    => $shop->id,
            'region_id'  => $region->id,
            'country_id' => $countryProduct->id,
            'type'       => ShopLocation::PRODUCT,
        ]);

        $resolvedService = $shop->checkoutCountry(ShopLocation::SERVICE);
        $resolvedProduct = $shop->checkoutCountry(ShopLocation::PRODUCT);

        $this->assertSame($countryService->id, $resolvedService->id);
        $this->assertSame($currencyA->id, $resolvedService->currency_id);

        $this->assertSame($countryProduct->id, $resolvedProduct->id);
        $this->assertSame($currencyB->id, $resolvedProduct->currency_id);
    }

    public function test_checkout_country_is_null_when_unconfigured(): void
    {
        $shop = $this->makeShop(); // no ShopLocation rows at all

        $this->assertNull($shop->checkoutCountry(ShopLocation::SERVICE));
        $this->assertNull($shop->checkoutCountry(ShopLocation::PRODUCT));
    }

    public function test_checkout_country_is_null_when_country_has_no_currency(): void
    {
        $region  = Region::query()->create(['active' => true]);
        $country = $this->makeCountry('NC', null); // no currency configured
        $shop    = $this->makeShop();

        ShopLocation::query()->create([
            'shop_id'    => $shop->id,
            'region_id'  => $region->id,
            'country_id' => $country->id,
            'type'       => ShopLocation::PRODUCT,
        ]);

        $this->assertNull($shop->checkoutCountry(ShopLocation::PRODUCT));
    }

    public function test_active_payment_ids_scope_to_country_plus_always_on_methods(): void
    {
        $currency = Currency::factory()->create(['title' => 'X', 'default' => true]);
        $country  = $this->makeCountry('ZZ', $currency);
        $other    = $this->makeCountry('YY', $currency);

        $stripe = Payment::factory()->create(['active' => true, 'tag' => Payment::TAG_STRIPE]);
        $paypal = Payment::factory()->create(['active' => true, 'tag' => Payment::TAG_PAY_PAL]);
        $wallet = Payment::factory()->create(['active' => true, 'tag' => Payment::TAG_WALLET]);
        $cash   = Payment::factory()->create(['active' => true, 'tag' => Payment::TAG_CASH]);

        // Only stripe enabled for this country; paypal enabled for the OTHER country only.
        $country->payments()->sync([$stripe->id => ['active' => true]]);
        $other->payments()->sync([$paypal->id => ['active' => true]]);

        $ids = $country->activePaymentIds();

        $this->assertTrue($ids->contains($stripe->id));
        $this->assertFalse($ids->contains($paypal->id));
        $this->assertTrue($ids->contains($wallet->id), 'wallet is always available regardless of country scoping');
        $this->assertTrue($ids->contains($cash->id), 'cash is always available regardless of country scoping');
    }

    public function test_active_payment_ids_excludes_a_gateway_disabled_for_this_country(): void
    {
        $currency = Currency::factory()->create(['title' => 'X', 'default' => true]);
        $country  = $this->makeCountry('ZZ', $currency);

        $stripe = Payment::factory()->create(['active' => true, 'tag' => Payment::TAG_STRIPE]);

        $country->payments()->sync([$stripe->id => ['active' => false]]);

        $this->assertFalse($country->activePaymentIds()->contains($stripe->id));
    }
}
