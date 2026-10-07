<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * For models with their own country_id column (ShopLocation, Warehouse,
 * DeliveryPoint, City, Area) — the simplest case, no join required.
 */
trait HasDirectCountryColumn
{
    use HasCountryRestriction;

    public function scopeInCountry(Builder $query, int $countryId): Builder
    {
        return $query->where('country_id', $countryId);
    }
}
