<?php
declare(strict_types=1);

namespace App\Models;

use App\Traits\Loadable;
use App\Traits\Regions;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

/**
 * App\Models\Country
 *
 * @property int $id
 * @property string|null $code
 * @property int|null $region_id
 * @property int|null $currency_id
 * @property boolean $active
 * @property string|null $img
 * @property Currency|null $currency
 * @property City|null $city
 * @property Collection|City[] $cities
 * @property Area|null $area
 * @property Collection|Area[] $areas
 * @property int|null $cities_count
 * @property Collection|CountryTranslation[] $translations
 * @property CountryTranslation|null $translation
 * @property int|null $translations_count
 * @property Collection|DeliveryPrice[] $deliveryPrices
 * @property DeliveryPrice|null $deliveryPrice
 * @property int|null $delivery_price_count
 * @property Collection|Payment[] $payments
 * @method static Builder|self active()
 * @method static Builder|self filter(array $filter)
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereId($value)
 * @mixin Eloquent
 */
class Country extends Model
{
    use Loadable, Regions;

    public $guarded    = ['id'];
    public $timestamps = false;

    public $casts = [
        'active' => 'bool',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'country_payments')
            ->withPivot('active')
            ->withTimestamps();
    }

    /**
     * Payment ids available at checkout for this country: the gateways
     * explicitly enabled for it, plus cash/wallet, which are always
     * available everywhere and are never country-scoped.
     */
    public function activePaymentIds(): Collection
    {
        $countryScoped = $this->payments()
            ->wherePivot('active', true)
            ->where('payments.active', true)
            ->pluck('payments.id');

        $alwaysOn = Payment::query()
            ->where('active', true)
            ->whereIn('tag', [Payment::TAG_CASH, Payment::TAG_WALLET])
            ->pluck('id');

        return $countryScoped->merge($alwaysOn)->unique()->values();
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CountryTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(CountryTranslation::class);
    }

    public function city(): HasOne
    {
        return $this->hasOne(City::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function area(): HasOne
    {
        return $this->hasOne(Area::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function deliveryPrice(): HasOne
    {
        return $this->hasOne(DeliveryPrice::class);
    }

    public function deliveryPrices(): HasMany
    {
        return $this->hasMany(DeliveryPrice::class);
    }

    public function scopeActive($query): Builder
    {
        /** @var Country $query */
        return $query->where('active', true);
    }

    public function scopeFilter($query, array $filter): void
    {
        $query
            ->when(request()->is('api/v1/rest/*') && request('lang'), function ($q) {
                $q->whereHas('translation', fn($query) => $query->where(function ($q) {

                    

                    $q->where('locale', request('lang'));

                }));
            })
            ->when(data_get($filter, 'code'), fn($q, $code) => $q->where('code', $code))
            ->when(data_get($filter, 'region_id'), fn($q, $regionId) => $q->where('region_id', $regionId))
            ->when(data_get($filter, 'has_price'),  fn($q) => $q->whereHas('deliveryPrice'))
            ->when(isset($filter['active']), fn($q) => $q->where('active', $filter['active']))
            ->when(data_get($filter, 'search'), function ($query, $search) {
                $query->whereHas('translations', function ($q) use ($search) {
                    $q
                        ->where(fn($q) => $q->where('title', 'LIKE', "%$search%")->orWhere('id', $search))
                        ->select('id', 'country_id', 'locale', 'title');
                });
            });
    }
}
