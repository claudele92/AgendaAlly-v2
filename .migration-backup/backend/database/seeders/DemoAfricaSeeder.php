<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\DeliveryPrice;
use App\Models\Language;
use App\Models\Region;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Traits\Loggable;
use Illuminate\Database\Seeder;
use Throwable;

/**
 * Demo geography for Cameroon (XAF) and Burkina Faso (XOF), both under
 * Africa, plus the ShopLocation rows that put the two demo sellers' shops
 * (see UserSeeder, users 107 and 113) into those countries — a shop has no
 * country_id column of its own; Shop::checkoutCountry() resolves it
 * entirely from productLocation()/serviceLocation(), both HasOne
 * ShopLocation scoped by type. Both of user 107's branches share the same
 * type and country (Cameroon), so they can't trip
 * ShopLocationService::conflictingCountryLocation()'s same-country-per-type
 * rule.
 *
 * Also seeds DeliveryPrice rows for both countries — without these, the
 * storefront's checkout can't offer a delivery option at all for either
 * demo shop (both are DELIVERY_TYPE_IN_HOUSE, so OrderHelper::deliveryPrice()
 * looks the price up directly by region/country/city, not by shop_id) and
 * the cart/checkout screens are unreachable past the shipping step.
 *
 * Deliberately NOT part of RegionSeeder. RegionSeeder is disabled (see
 * DatabaseSeeder) and populates the entire world from countries.json — a
 * separate, larger, previously-flagged-but-not-yet-decided change. This is
 * a small, self-contained addition of just the demo data this task needs,
 * safe to run whether or not RegionSeeder ever gets enabled: it looks up
 * every row by translation title before creating (same idempotency
 * pattern RegionSeeder itself uses), so it no-ops cleanly against a
 * RegionSeeder-populated database that already has these countries, and
 * never creates a duplicate Africa region on a second run.
 */
class DemoAfricaSeeder extends Seeder
{
    use Loggable;

    // See UserSeeder — the two demo sellers' user ids.
    private const CAMEROON_SELLER_USER_ID = 107;
    private const BURKINA_FASO_SELLER_USER_ID = 113;

    public function run(): void
    {
        try {
            $locale = data_get(Language::where('default', 1)->first(), 'locale', 'en');

            $africa = $this->region('Africa', $locale);

            $cameroon = $this->country($africa, 'Cameroon', 'cm', 'XAF', $locale);
            $douala   = $this->city($africa, $cameroon, 'Douala', $locale);
            $yaounde  = $this->city($africa, $cameroon, 'Yaoundé', $locale);

            $burkinaFaso   = $this->country($africa, 'Burkina Faso', 'bf', 'XOF', $locale);
            $ouagadougou   = $this->city($africa, $burkinaFaso, 'Ouagadougou', $locale);
            $boboDioulasso = $this->city($africa, $burkinaFaso, 'Bobo-Dioulasso', $locale);

            $cameroonShop = Shop::where('user_id', self::CAMEROON_SELLER_USER_ID)->first();

            if ($cameroonShop) {
                $this->shopLocation($cameroonShop, $africa, $cameroon, $yaounde, ShopLocation::PRODUCT);
                $this->shopLocation($cameroonShop, $africa, $cameroon, $douala, ShopLocation::PRODUCT);

                // Same two cities, but as SERVICE-type locations — a
                // structurally separate ShopLocation row per type even at
                // the same city (see shopLocation()'s match key below), so
                // a master/staff member managing "the Douala branch"
                // end-to-end can be assigned to both.
                $this->shopLocation($cameroonShop, $africa, $cameroon, $yaounde, ShopLocation::SERVICE);
                $this->shopLocation($cameroonShop, $africa, $cameroon, $douala, ShopLocation::SERVICE);
            }

            $burkinaShop = Shop::where('user_id', self::BURKINA_FASO_SELLER_USER_ID)->first();

            if ($burkinaShop) {
                $this->shopLocation($burkinaShop, $africa, $burkinaFaso, $ouagadougou, ShopLocation::PRODUCT);

                // Missing until now — DemoServiceCatalogSeeder (added later,
                // in a separate PR) seeds real bookable services for this
                // shop too, but without this SERVICE-type location row it
                // could never surface in the storefront's service search at
                // all: that search always queries location_type=2/SERVICE,
                // and scopeFilter()'s location match requires an exact type
                // hit — Cameroon's shop already had both types (see above).
                $this->shopLocation($burkinaShop, $africa, $burkinaFaso, $ouagadougou, ShopLocation::SERVICE);
            }

            // Bobo-Dioulasso is seeded per the spec (a second Burkina Faso
            // city to pick from) but isn't assigned to a shop — only one
            // Burkina Faso branch was asked for. It's also deliberately
            // left without a DeliveryPrice row below, for the same reason.
            unset($boboDioulasso);

            // A country-level fallback (city_id null) alongside each seeded
            // city: DeliveryPrice::filter() falls back to the null-city row
            // whenever a request narrows by country_id but hasn't got a
            // city_id yet (see DeliveryPrice::scopeFilter()'s whereNull
            // defaults), which is exactly the state the storefront's
            // shipping step queries in before the customer's city is known.
            $this->deliveryPrice($africa, $cameroon, null);
            $this->deliveryPrice($africa, $cameroon, $douala);
            $this->deliveryPrice($africa, $cameroon, $yaounde);

            $this->deliveryPrice($africa, $burkinaFaso, null);
            $this->deliveryPrice($africa, $burkinaFaso, $ouagadougou);
        } catch (Throwable $e) {
            $this->error($e);
        }
    }

    private function region(string $title, string $locale): Region
    {
        $region = Region::whereHas('translation', fn($q) => $q->where('locale', $locale)->where('title', $title))->first();

        if (!$region) {
            $region = Region::create(['active' => true]);
            $region->translations()->create(['title' => $title, 'locale' => $locale]);
            $this->command?->info("region: $title");
        }

        return $region;
    }

    private function country(Region $region, string $title, string $iso2, string $currencyTitle, string $locale): Country
    {
        $currencyId = Currency::where('title', $currencyTitle)->value('id');

        $country = Country::whereHas('translation', fn($q) => $q->where('locale', $locale)->where('title', $title))->first();

        if (!$country) {
            $country = Country::create([
                'region_id'   => $region->id,
                'code'        => $iso2,
                'active'      => true,
                'img'         => "https://flagcdn.com/h120/$iso2.png",
                'currency_id' => $currencyId,
            ]);
            $country->translations()->create(['title' => $title, 'locale' => $locale]);
            $this->command?->info("country: $title");
        } elseif (!$country->currency_id && $currencyId) {
            $country->update(['currency_id' => $currencyId]);
        }

        return $country;
    }

    private function city(Region $region, Country $country, string $title, string $locale): City
    {
        $city = City::whereHas('translation', fn($q) => $q->where('locale', $locale)->where('title', $title))->first();

        if (!$city) {
            $city = City::create([
                'active'     => true,
                'region_id'  => $region->id,
                'country_id' => $country->id,
            ]);
            $city->translations()->create(['title' => $title, 'locale' => $locale]);
            $this->command?->info("city: $title");
        }

        return $city;
    }

    private function shopLocation(Shop $shop, Region $region, Country $country, City $city, int $type): void
    {
        // 'type' is part of the match key deliberately — without it, a
        // second call for the same shop_id/city_id but a different type
        // (e.g. seeding SERVICE after PRODUCT already exists) would
        // silently flip the existing row's type instead of creating a
        // second one, corrupting the first location it seeded.
        ShopLocation::updateOrCreate([
            'shop_id' => $shop->id,
            'city_id' => $city->id,
            'type'    => $type,
        ], [
            'region_id'  => $region->id,
            'country_id' => $country->id,
        ]);
    }

    private function deliveryPrice(Region $region, Country $country, ?City $city): void
    {
        $deliveryPrice = DeliveryPrice::where('region_id', $region->id)
            ->where('country_id', $country->id)
            ->when($city, fn($q) => $q->where('city_id', $city->id), fn($q) => $q->whereNull('city_id'))
            ->first();

        if (!$deliveryPrice) {
            $deliveryPrice = DeliveryPrice::create([
                // "real-world $ * 600" (600 = the currency:rebase-to-xaf
                // factor, see CurrencySeeder) - a bare 2 here would mean 2
                // XAF (~$0.003) now that XAF is the base currency, not $2.
                'price'      => 2 * 600,
                'region_id'  => $region->id,
                'country_id' => $country->id,
                'city_id'    => $city?->id,
            ]);
            $deliveryPrice->translations()->create(['title' => 'Standard delivery', 'locale' => 'en']);
            $this->command?->info("delivery price: {$country->code}" . ($city ? "/{$city->id}" : ''));
        }
    }
}
