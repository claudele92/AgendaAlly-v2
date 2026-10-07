<?php

declare(strict_types=1);

namespace Tests\Feature\Currency;

use App\Models\AdsPackage;
use App\Models\Category;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Region;
use App\Models\Service;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Models\Stock;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Request as RequestFacade;
use Tests\TestCase;

/**
 * Phase 1 of the multi-currency work: a shared Currency::convert() helper,
 * used by AdsPackage/Subscription's seller-facing display (both previously
 * had no conversion at all; both are read-only for sellers — browse/
 * purchase, never edited — so converting for display is safe).
 *
 * Stock (product price) and Service originally got the same seller-facing
 * conversion applied to their existing customer-facing accessors, but that
 * was reverted: every seller-facing consumer of those prices is an
 * editable create/edit form whose submit path resends the displayed value
 * verbatim as the new raw price — converting for display there meant a
 * seller editing an existing product/service (even without touching the
 * price field) would silently resave it inflated by their currency's rate.
 * See test_stock_and_service_prices_stay_raw_on_seller_paths below, which
 * guards against reintroducing that.
 *
 * Order/Booking/Cart/ParcelOrder have their own, separate, frozen-rate
 * conversion for customer-facing paths — untouched by any of this.
 */
class SellerCurrencyConversionTest extends TestCase
{
    use RefreshDatabase;

    private function fakeSellerRequest(): void
    {
        RequestFacade::swap(\Illuminate\Http\Request::create('/api/v1/dashboard/seller/products/paginate', 'GET'));
    }

    private function fakeAdminRequest(): void
    {
        RequestFacade::swap(\Illuminate\Http\Request::create('/api/v1/dashboard/admin/products/paginate', 'GET'));
    }

    /** @return array{0: Shop, 1: Currency, 2: User} */
    private function makeShopWithCountryCurrency(string $currencyTitle, float $rate): array
    {
        $currency = Currency::query()->create(['title' => $currencyTitle, 'symbol' => $currencyTitle, 'rate' => $rate, 'default' => 0, 'active' => 1]);
        $region   = Region::query()->create(['active' => true]);
        $country  = Country::query()->create(['region_id' => $region->id, 'active' => true, 'currency_id' => $currency->id]);

        $user = User::factory()->create();
        $shop = Shop::factory()->create([
            'user_id'       => $user->id,
            'type'          => 1,
            'delivery_time' => ['type' => 'minute', 'from' => 30, 'to' => 60],
        ]);

        ShopLocation::query()->create([
            'shop_id'    => $shop->id,
            'region_id'  => $region->id,
            'country_id' => $country->id,
            'type'       => ShopLocation::PRODUCT,
        ]);

        return [$shop, $currency, $user];
    }

    public function test_currency_convert_is_a_no_op_when_the_target_currency_is_unresolved(): void
    {
        Currency::query()->create(['title' => 'USD', 'symbol' => '$', 'rate' => 1, 'default' => 1, 'active' => 1]);

        $this->assertSame(250.0, Currency::convert(250.0, null));
        $this->assertSame(250.0, Currency::convert(250.0, 999999));
    }

    public function test_currency_convert_applies_the_usd_relative_rate_ratio(): void
    {
        Currency::query()->create(['title' => 'USD', 'symbol' => '$', 'rate' => 1, 'default' => 1, 'active' => 1]);
        $xaf = Currency::query()->create(['title' => 'XAF', 'symbol' => 'FCFA', 'rate' => 600, 'default' => 0, 'active' => 1]);

        $this->assertSame(150000.0, Currency::convert(250.0, $xaf->id));
    }

    /**
     * Regression guard: Stock/Service's seller-facing price must stay raw
     * and unconverted, because every current seller-facing consumer is an
     * editable form whose submit path resends this same value as the new
     * price — converting it for display would silently inflate the stored
     * price on every edit for a non-platform-default-currency shop. See
     * the class docblock.
     */
    public function test_stock_and_service_prices_stay_raw_on_seller_paths(): void
    {
        Currency::query()->create(['title' => 'USD', 'symbol' => '$', 'rate' => 1, 'default' => 1, 'active' => 1]);
        [$shop] = $this->makeShopWithCountryCurrency('XAF', 600);

        $category = Category::factory()->create();
        $product  = Product::factory()->create(['shop_id' => $shop->id, 'category_id' => $category->id]);
        $stock    = Stock::query()->create(['product_id' => $product->id, 'price' => 250, 'quantity' => 10]);
        $service  = Service::query()->create(['shop_id' => $shop->id, 'category_id' => $category->id, 'price' => 100]);

        $this->fakeSellerRequest();
        $this->assertSame(250.0, (float) $stock->fresh()->rate_price);
        $this->assertSame(100.0, (float) $service->fresh()->rate_price);

        $this->fakeAdminRequest();
        $this->assertSame(250.0, (float) $stock->fresh()->rate_price);
        $this->assertSame(100.0, (float) $service->fresh()->rate_price);
    }

    public function test_subscription_price_and_currency_match_the_viewing_sellers_own_shop(): void
    {
        Currency::query()->create(['title' => 'USD', 'symbol' => '$', 'rate' => 1, 'default' => 1, 'active' => 1]);
        [, $xaf, $user] = $this->makeShopWithCountryCurrency('XAF', 600);

        $subscription = Subscription::query()->create(['type' => 'shop', 'price' => 250, 'month' => 1, 'active' => true, 'title' => 'Starter']);

        $this->actingAs($user, 'sanctum');
        $this->fakeSellerRequest();
        $this->assertSame(150000.0, $subscription->fresh()->rate_price);
        $this->assertSame($xaf->id, $subscription->fresh()->rate_currency->id);

        $this->fakeAdminRequest();
        $this->assertSame(250.0, $subscription->fresh()->rate_price);
        $this->assertSame('USD', $subscription->fresh()->rate_currency->title);
    }

    public function test_ads_package_price_and_currency_match_the_viewing_sellers_own_shop(): void
    {
        Currency::query()->create(['title' => 'USD', 'symbol' => '$', 'rate' => 1, 'default' => 1, 'active' => 1]);
        [, $xof, $user] = $this->makeShopWithCountryCurrency('XOF', 600);

        $adsPackage = AdsPackage::query()->create(['active' => true, 'type' => AdsPackage::MAIN, 'time_type' => 'day', 'time' => 1, 'price' => 100]);

        $this->actingAs($user, 'sanctum');
        $this->fakeSellerRequest();
        $this->assertSame(60000.0, $adsPackage->fresh()->rate_price);
        $this->assertSame($xof->id, $adsPackage->fresh()->rate_currency->id);

        $this->fakeAdminRequest();
        $this->assertSame(100.0, $adsPackage->fresh()->rate_price);
        $this->assertSame('USD', $adsPackage->fresh()->rate_currency->title);
    }
}
