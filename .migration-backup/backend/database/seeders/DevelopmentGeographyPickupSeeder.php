<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Console\Commands\DevelopmentDatabaseGuard;
use App\Helpers\Utility;
use App\Models\Area;
use App\Models\DeliveryPoint;
use App\Models\DeliveryPointWorkingDay;
use App\Models\DeliveryPrice;
use App\Models\Language;
use App\Models\Shop;
use App\Models\ShopLocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Local-only Douala area, delivery-price, and pickup fixtures for the
 * Cameroon development shop. These rows support the existing checkout flow;
 * they do not configure external maps or change production delivery rules.
 */
class DevelopmentGeographyPickupSeeder extends Seeder
{
    private const CAMEROON_SELLER_USER_ID = 107;
    private const AREA_TITLE = 'Douala Demo Area';

    public function run(): void
    {
        $basePath = base_path();

        DevelopmentDatabaseGuard::requireOptIn(
            (string) config('app.env'),
            env('AGENDAALLY_DEVELOPMENT_DATABASE'),
            env('DEVELOPMENT_MODE')
        );

        $manifest = DevelopmentDatabaseGuard::reviewedManifest($basePath);
        $path = DevelopmentDatabaseGuard::resolveSqlitePath(
            $basePath,
            (string) config('database.default', ''),
            (array) config('database.connections.sqlite', [])
        );

        if (!DevelopmentDatabaseGuard::ownsDatabase($path, $basePath, $manifest)) {
            throw new RuntimeException(
                'The local geography and pickup fixtures require the owned development SQLite marker.'
            );
        }

        DB::transaction(function (): void {
            $this->seedDoualaFixtures();
        });
    }

    private function seedDoualaFixtures(): void
    {
        $shop = Shop::query()
            ->where('user_id', self::CAMEROON_SELLER_USER_ID)
            ->first();

        if (!$shop) {
            throw new RuntimeException('The Cameroon demo shop is required before its pickup fixtures can be seeded.');
        }

        $location = $shop->locations()
            ->whereHas('city.translations', fn ($query) => $query->where('title', 'Douala'))
            ->orderBy('id')
            ->first();

        if (!$location || !$location->city || !$location->country || !$location->region) {
            throw new RuntimeException(
                'The Cameroon demo shop must have a complete Douala location before its pickup fixtures can be seeded.'
            );
        }

        $city = $location->city;
        $country = $location->country;
        $region = $location->region;
        $defaultLocale = Language::query()->where('default', 1)->value('locale') ?: 'en';
        $locales = array_values(array_unique(['en', $defaultLocale]));

        $area = Area::query()
            ->where('city_id', $city->id)
            ->whereHas('translations', fn ($query) => $query
                ->where('locale', 'en')
                ->where('title', self::AREA_TITLE))
            ->first();

        if (!$area) {
            $area = Area::query()->create([
                'active' => true,
                'region_id' => $region->id,
                'country_id' => $country->id,
                'city_id' => $city->id,
            ]);
        } else {
            $area->update([
                'active' => true,
                'region_id' => $region->id,
                'country_id' => $country->id,
                'city_id' => $city->id,
            ]);
        }

        foreach ($locales as $locale) {
            $area->translations()->updateOrCreate(
                ['locale' => $locale],
                ['title' => self::AREA_TITLE]
            );
        }

        // Keep the selected Douala branch consistent with the area returned
        // by the same location filters used by storefront search.
        ShopLocation::query()
            ->where('shop_id', $shop->id)
            ->where('city_id', $city->id)
            ->update(['area_id' => $area->id]);

        $cityDeliveryPrice = DeliveryPrice::query()
            ->where('region_id', $region->id)
            ->where('country_id', $country->id)
            ->where('city_id', $city->id)
            ->whereNull('area_id')
            ->whereNull('shop_id')
            ->first();

        if (!$cityDeliveryPrice) {
            throw new RuntimeException(
                'A city-level Douala delivery price is required to derive the local area delivery price.'
            );
        }

        $areaDeliveryPrice = DeliveryPrice::query()->firstOrCreate(
            [
                'region_id' => $region->id,
                'country_id' => $country->id,
                'city_id' => $city->id,
                'area_id' => $area->id,
                'shop_id' => null,
            ],
            ['price' => $cityDeliveryPrice->price]
        );

        foreach ($locales as $locale) {
            $areaDeliveryPrice->translations()->updateOrCreate(
                ['locale' => $locale],
                ['title' => 'Local demo delivery']
            );
        }

        $latitude = (float) $shop->latitude;
        $longitude = (float) $shop->longitude;

        if (
            !is_finite($latitude)
            || !is_finite($longitude)
            || $latitude < -90
            || $latitude > 90
            || $longitude < -180
            || $longitude > 180
        ) {
            throw new RuntimeException(
                'The Cameroon demo shop must have valid coordinates before its local pickup option can be seeded.'
            );
        }

        $shopTitle = $shop->translations()
            ->where('locale', $defaultLocale)
            ->value('title') ?: 'Cameroon demo shop';
        $pointTitle = "{$shopTitle} — Douala demo pickup";
        $pointAddress = 'Douala, Cameroon (local development pickup fixture)';

        $deliveryPoint = DeliveryPoint::query()
            ->where('region_id', $region->id)
            ->where('country_id', $country->id)
            ->where('city_id', $city->id)
            ->where('area_id', $area->id)
            ->whereHas('translations', fn ($query) => $query
                ->where('locale', 'en')
                ->where('title', $pointTitle))
            ->first();

        $pointAttributes = [
            'active' => true,
            'region_id' => $region->id,
            'country_id' => $country->id,
            'city_id' => $city->id,
            'area_id' => $area->id,
            'price' => 0,
            'address' => array_fill_keys($locales, $pointAddress),
            'location' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
            'fitting_rooms' => 0,
        ];

        if ($deliveryPoint) {
            $deliveryPoint->update($pointAttributes);
        } else {
            $deliveryPoint = DeliveryPoint::query()->create($pointAttributes);
        }

        foreach ($locales as $locale) {
            $deliveryPoint->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title' => $pointTitle,
                    'description' => 'Free local pickup for the Cameroon demo shop. This fixture does not use live maps or external services.',
                ]
            );
        }

        foreach (Utility::DAYS as $day) {
            DeliveryPointWorkingDay::query()->updateOrCreate(
                [
                    'delivery_point_id' => $deliveryPoint->id,
                    'day' => $day,
                ],
                [
                    'from' => '09:00',
                    'to' => '18:00',
                    'disabled' => false,
                ]
            );
        }
    }
}