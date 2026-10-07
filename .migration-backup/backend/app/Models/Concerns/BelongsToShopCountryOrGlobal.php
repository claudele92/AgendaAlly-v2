<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * For models with a nullable shop_id (Category, Brand, PropertyGroup):
 * platform-wide catalog entries (shop_id null) stay visible to every
 * country admin, in addition to shop-specific ones belonging to their
 * own country.
 */
trait BelongsToShopCountryOrGlobal
{
    use HasCountryRestriction;

    public function scopeInCountry(Builder $query, int $countryId): Builder
    {
        return $query->where(function (Builder $q) use ($countryId) {
            $q->whereNull('shop_id')
                ->orWhereHas('shop.locations', fn ($q2) => $q2->where('country_id', $countryId));
        });
    }
}
