<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Scopes\CountryRestrictionScope;

/**
 * Registers the country-restriction global scope on a model. The model
 * must define its own scopeInCountry(Builder $query, int $countryId)
 * local scope — this trait only wires it up to auto-apply for restricted
 * country admins. See BelongsToShopCountry for models that relate to a
 * country through a shop.
 */
trait HasCountryRestriction
{
    public static function bootHasCountryRestriction(): void
    {
        static::addGlobalScope(new CountryRestrictionScope());
    }
}
