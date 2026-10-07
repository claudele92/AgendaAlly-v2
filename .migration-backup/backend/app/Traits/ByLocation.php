<?php
declare(strict_types=1);

namespace App\Traits;

use App\Models\Language;
use App\Models\ShopLocation;
use App\Helpers\PortableSql;
use DB;
use InvalidArgumentException;

/**
 * @property string|null $language
 */
trait ByLocation
{
    public function hasValidCoordinates(mixed $latitude, mixed $longitude): bool
    {
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            return false;
        }

        $latitude = (float) $latitude;
        $longitude = (float) $longitude;

        return is_finite($latitude)
            && is_finite($longitude)
            && $latitude >= -90
            && $latitude <= 90
            && $longitude >= -180
            && $longitude <= 180;
    }

    public function getShopIds(array $filter): array
    {
        $regionId   = $filter['region_id']  ?? null;
        $countryId  = $filter['country_id'] ?? null;
        $cityId     = $filter['city_id']    ?? null;
        $areaId     = $filter['area_id']    ?? null;
        $type       = data_get($filter, 'location_type');

        $byLocation = $regionId || $countryId || $cityId || $areaId;

        if (!$byLocation) {
            return [];
        }

//        $key = 'shop_locations';
//
//        switch (true) {
//            case !empty($regionId):
//                $key .= "_$regionId";
//                break;
//            case !empty($countryId):
//                $key .= "_$countryId";
//                break;
//            case !empty($cityId):
//                $key .= "_$cityId";
//                break;
//            case !empty($areaId):
//                $key .= "_$areaId";
//                break;
//        }

        return DB::table('shop_locations')
            ->where('region_id', $regionId)
            ->when($type,      fn($q) => $q->where( fn($q) => $q->where('type',       $type)))
            ->when($countryId, fn($q) => $q->where( fn($q) => $q->where('country_id', $countryId)->orWhereNull('country_id') ) )
            ->when($cityId,    fn($q) => $q->where( fn($q) => $q->where('city_id',    $cityId)->orWhereNull('city_id') ) )
            ->when($areaId,    fn($q) => $q->where( fn($q) => $q->where('area_id',    $areaId)->orWhereNull('area_id') ) )
            ->pluck('shop_id')
            ->unique()
            ->values()
            ->toArray();
    }

    public function search(mixed $query, ?string $search): array
    {
        return $query->where(function ($query) use ($search) {
            $query
                ->whereHas('region.translation', function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%$search%");
                })
                ->orWhereHas('country.translation', function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%$search%");
                })
                ->orWhereHas('city.translation', function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%$search%");
                })
                ->orWhereHas('area.translation', function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%$search%");
                });
        });
    }

    public function getWith(): array
    {
        if (!isset($this->language)) {
            $locale = Language::where('default', 1)->first()?->locale;
            $this->language = $locale;
        }

        return [
            'region.translation'  => fn($query) => $query->where('locale', $this->language),
            'country.translation' => fn($query) => $query->where('locale', $this->language),
            'city.translation'    => fn($query) => $query->where('locale', $this->language),
            'area.translation'    => fn($query) => $query->where('locale', $this->language),
        ];
    }

    /**
     * A shop with branches in more than one region/country/city/area (see
     * Shop::scopeFilter()'s whereHas('locations', ...)) correctly matches a
     * location-filtered search via ANY of its locations, but shops.latitude/
     * shops.longitude is a single flat pair set once from whichever location
     * existed when the shop profile was created/edited (see ShopService::
     * setShopParams()) - it can describe a different branch than the one
     * that actually matched, making distance-to-customer wrong by however
     * far apart the two branches are. This is the same root cause
     * ShopResource::matched_location works around for the address string;
     * mirrors its exact matching (region/country/city/area, first match by
     * id, and the same location_type a search asked for, defaulting to
     * SERVICE when omitted so this never measures from a same-city
     * location of the WRONG type, e.g. a shop's empty PRODUCT-type
     * placeholder instead of its real SERVICE branch; see DemoAfricaSeeder)
     * so the two never describe different locations for the same shop,
     * falling back to the shop's own flat pair when no location filter was
     * given, or no location happens to match one that was.
     */
    public function distanceSelectRaw(
        array $filter,
        float $longitude,
        float $latitude,
        ?string $driver = null
    ): string
    {
        if (!$this->hasValidCoordinates($latitude, $longitude)) {
            throw new InvalidArgumentException('Invalid customer geographic coordinates.');
        }

        $geoConditions = collect([
            'region_id'  => data_get($filter, 'region_id'),
            'country_id' => data_get($filter, 'country_id'),
            'city_id'    => data_get($filter, 'city_id'),
            'area_id'    => data_get($filter, 'area_id'),
        ])->filter();

        if ($geoConditions->isEmpty()) {
            return PortableSql::distanceKilometers(
                'shops.longitude',
                'shops.latitude',
                $longitude,
                $latitude,
                $driver
            );
        }

        // Default to SERVICE when the caller omits location_type, same as
        // ShopResource::matched_location - this is a booking marketplace,
        // so an empty PRODUCT placeholder is never the branch a distance
        // calculation should measure from.
        $geoConditions->put('type', (int) (data_get($filter, 'location_type') ?: ShopLocation::SERVICE));

        $conditions = $geoConditions
            ->map(fn($id, $column) => "$column = " . (int)$id)
            ->implode(' AND ');

        $matchedLongitude = "(SELECT longitude FROM shop_locations WHERE shop_id = shops.id AND $conditions ORDER BY id LIMIT 1)";
        $matchedLatitude  = "(SELECT latitude FROM shop_locations WHERE shop_id = shops.id AND $conditions ORDER BY id LIMIT 1)";

        return PortableSql::distanceKilometers(
            "COALESCE($matchedLongitude, shops.longitude)",
            "COALESCE($matchedLatitude, shops.latitude)",
            $longitude,
            $latitude,
            $driver
        );
    }

    public function getIds(array $filter): array
    {
        $regionId   = @$filter['region_id'];
        $countryId  = @$filter['country_id'];
        $cityId     = @$filter['city_id'];
        $areaId     = @$filter['area_id'];
        $byLocation = $regionId || $countryId || $cityId || $areaId;

        $shopIds = [];

        if ($byLocation) {
            $shopIds = $this->getShopIds(request()->all());
        }

        if (isset($filter['shop_id'])) {
            $shopIds = (array)$filter['shop_id'];
        }

        return $shopIds;
    }
}
