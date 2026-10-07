<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * For any model with a shop() relation: restricts it to shops located in
 * the acting country admin's country. Covers the large majority of
 * country-scoped models (bookings, orders, products, services, coupons,
 * etc.) with a single `use` statement — one line per model instead of a
 * bespoke scopeInCountry() each.
 */
trait BelongsToShopCountry
{
    use HasCountryRestriction;

    public function scopeInCountry(Builder $query, int $countryId): Builder
    {
        return $query->whereHas('shop.locations', fn ($q) => $q->where('country_id', $countryId));
    }
}
