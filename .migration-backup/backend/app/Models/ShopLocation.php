<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasDirectCountryColumn;
use App\Traits\Areas;
use App\Traits\Cities;
use App\Traits\Countries;
use App\Traits\Regions;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\ShopLocation
 *
 * @property int $id
 * @property int $shop_id
 * @property int $region_id
 * @property int|null $country_id
 * @property int|null $city_id
 * @property int|null $area_id
 * @property string|null $address
 * @property string|null $alias
 * @property float|null $latitude
 * @property float|null $longitude
 * @property int $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @mixin Eloquent
 */
class ShopLocation extends Model
{
    use Regions, Countries, Cities, Areas, HasDirectCountryColumn;

    protected $guarded = ['id'];

    const PRODUCT = 1;
    const SERVICE = 2;

    const TYPES = [
        self::PRODUCT => self::PRODUCT,
        self::SERVICE => self::SERVICE,
    ];

    /**
     * Keeps the seller's personal currency_id (what SetCurrency::currency()
     * uses for their dashboard/earnings display) in lockstep with their
     * shop's resolved country, mirroring how Shop::displayCurrency() already
     * resolves the storefront's currency from these same rows. Living here
     * rather than in the create/update service methods means it fires no
     * matter how a ShopLocation gets written - the seller API, an admin
     * action, or a seeder's updateOrCreate() - so a seller's currency is
     * never left to a manual per-seller fix.
     */
    protected static function booted(): void
    {
        static::saved(function (self $location) {
            /** @var Shop|null $shop */
            $shop = $location->shop;
            $currency = $shop?->displayCurrency();

            // A shop with no resolved country/currency yet (e.g. only the
            // opposite location type is set) is left alone rather than
            // reset to null - that would just fall back to the platform
            // default currency instead of leaving the seller's prior value.
            if ($currency) {
                $shop->seller()->update(['currency_id' => $currency->id]);
            }
        });
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function scopeFilter($query, array $filter) {
        $query
            ->when(data_get($filter, 'shop_id'),    fn($q, $shopId) => $q->where('shop_id',    $shopId))
            ->when(data_get($filter, 'type'),       fn($q, $type)   => $q->where('type',       $type))
            ->when(data_get($filter, 'region_id'),  fn($q, $id)     => $q->where('region_id',  $id))
            ->when(data_get($filter, 'country_id'), fn($q, $id)     => $q->where('country_id', $id))
            ->when(data_get($filter, 'city_id'),    fn($q, $id)     => $q->where('city_id',    $id))
            ->when(data_get($filter, 'area_id'),    fn($q, $id)     => $q->where('area_id',    $id));
    }

}
