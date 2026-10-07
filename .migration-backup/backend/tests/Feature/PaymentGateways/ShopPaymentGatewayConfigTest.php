<?php

declare(strict_types=1);

namespace Tests\Feature\PaymentGateways;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Payment;
use App\Models\Region;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Models\ShopPayment;
use App\Models\User;
use App\Repositories\ShopPaymentRepository\ShopPaymentRepository;
use App\Services\ShopServices\ShopPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShopPaymentGatewayConfigTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: Shop, 1: Country, 2: Payment} */
    private function makeShopCountryAndPayment(string $countryCurrencyTitle, bool $gatewayAvailable = true, string $paymentTag = Payment::TAG_ORANGE): array
    {
        $currency = Currency::query()->create(['title' => $countryCurrencyTitle, 'symbol' => $countryCurrencyTitle, 'rate' => 1, 'default' => 0, 'active' => 1]);
        $region   = Region::query()->create(['active' => true]);
        $country  = Country::query()->create(['region_id' => $region->id, 'active' => true, 'currency_id' => $currency->id]);

        $shop = Shop::factory()->create([
            'user_id'       => User::factory()->create()->id,
            'type'          => 1,
            'delivery_time' => ['type' => 'minute', 'from' => 30, 'to' => 60],
        ]);

        ShopLocation::query()->create([
            'shop_id'    => $shop->id,
            'region_id'  => $region->id,
            'country_id' => $country->id,
            'type'       => ShopLocation::PRODUCT,
        ]);

        $payment = Payment::query()->create(['tag' => $paymentTag, 'active' => true, 'input' => 15]);

        if ($gatewayAvailable) {
            $country->payments()->attach($payment->id, ['active' => true]);
        }

        return [$shop, $country, $payment];
    }

    public function test_currency_defaults_to_the_shops_country_currency_when_not_given(): void
    {
        [$shop, , $payment] = $this->makeShopCountryAndPayment('XOF');

        $result = (new ShopPaymentService())->create([
            'shop_id'      => $shop->id,
            'payment_id'   => $payment->id,
            'status'       => true,
            'merchant_key' => 'secret',
        ]);

        $this->assertTrue($result['status']);
        $this->assertSame('XOF', ShopPayment::where('shop_id', $shop->id)->value('currency'));
    }

    public function test_an_explicit_currency_override_is_respected(): void
    {
        [$shop, , $payment] = $this->makeShopCountryAndPayment('XOF');

        $result = (new ShopPaymentService())->create([
            'shop_id'      => $shop->id,
            'payment_id'   => $payment->id,
            'status'       => true,
            'merchant_key' => 'secret',
            'currency'     => 'XAF',
        ]);

        $this->assertTrue($result['status']);
        $this->assertSame('XAF', ShopPayment::where('shop_id', $shop->id)->value('currency'));
    }

    public function test_a_shop_cannot_configure_a_gateway_not_available_in_its_country(): void
    {
        [$shop, , $payment] = $this->makeShopCountryAndPayment('XOF', gatewayAvailable: false);

        $result = (new ShopPaymentService())->create([
            'shop_id'      => $shop->id,
            'payment_id'   => $payment->id,
            'status'       => true,
            'merchant_key' => 'secret',
        ]);

        $this->assertFalse($result['status']);
        $this->assertNull(ShopPayment::where('shop_id', $shop->id)->first());
    }

    public function test_a_shop_cannot_configure_a_non_credential_gateway_not_available_in_its_country(): void
    {
        // Prior to widening prepareGatewayConfig()'s country check beyond
        // Orange/MTN, this request would have been accepted unchecked —
        // a direct POST could add a gateway shopNonExist() correctly
        // excludes from the seller's "add payment method" list.
        [$shop, , $payment] = $this->makeShopCountryAndPayment('XOF', gatewayAvailable: false, paymentTag: Payment::TAG_STRIPE);

        $result = (new ShopPaymentService())->create([
            'shop_id'    => $shop->id,
            'payment_id' => $payment->id,
            'status'     => true,
            'client_id'  => 'pk_test_123',
        ]);

        $this->assertFalse($result['status']);
        $this->assertNull(ShopPayment::where('shop_id', $shop->id)->first());
    }

    public function test_a_non_credential_gateway_available_in_its_country_can_still_be_configured(): void
    {
        [$shop, , $payment] = $this->makeShopCountryAndPayment('XOF', gatewayAvailable: true, paymentTag: Payment::TAG_STRIPE);

        $result = (new ShopPaymentService())->create([
            'shop_id'    => $shop->id,
            'payment_id' => $payment->id,
            'status'     => true,
            'client_id'  => 'pk_test_123',
        ]);

        $this->assertTrue($result['status']);
        $this->assertNotNull(ShopPayment::where('shop_id', $shop->id)->first());
    }

    public function test_shop_non_exist_excludes_gateways_not_offered_in_the_shops_country(): void
    {
        [$shop, $country] = $this->makeShopCountryAndPayment('XOF', gatewayAvailable: false, paymentTag: Payment::TAG_ORANGE);

        $offered = Payment::query()->create(['tag' => Payment::TAG_STRIPE, 'active' => true, 'input' => 15]);
        $country->payments()->attach($offered->id, ['active' => true]);

        $listed = (new ShopPaymentRepository())->shopNonExist($shop)->pluck('tag');

        $this->assertTrue($listed->contains(Payment::TAG_STRIPE));
        $this->assertFalse($listed->contains(Payment::TAG_ORANGE));
    }

    public function test_credentials_are_encrypted_at_rest_not_stored_as_plaintext(): void
    {
        [$shop, , $payment] = $this->makeShopCountryAndPayment('XOF');

        (new ShopPaymentService())->create([
            'shop_id'      => $shop->id,
            'payment_id'   => $payment->id,
            'status'       => true,
            'merchant_key' => 'super-secret-value',
        ]);

        $rawValue = DB::table('shop_payments')->where('shop_id', $shop->id)->value('merchant_key');
        $this->assertNotSame('super-secret-value', $rawValue, 'raw DB column must not hold the plaintext secret');

        $decrypted = ShopPayment::where('shop_id', $shop->id)->first();
        $this->assertSame('super-secret-value', $decrypted->merchant_key, 'the model must decrypt it back correctly');
    }
}
