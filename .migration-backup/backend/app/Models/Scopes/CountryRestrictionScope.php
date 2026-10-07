<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Helpers\CountryContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Auto-applies a country restriction to every query against a model using
 * HasCountryRestriction, when the acting user is a restricted country
 * admin. Unrestricted users (superadmins, sellers, customers, anyone with
 * no CountryAdmin row) get no filter at all.
 *
 * Delegates the actual filtering to the model's own scopeInCountry() local
 * scope, since how a model relates to a country differs per model (a
 * direct country_id column, a shop's location, a polymorphic payable,
 * etc.) — see BelongsToShopCountry for the common shop-relation case.
 */
class CountryRestrictionScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $countryId = CountryContext::restrictedCountryId();

        if ($countryId !== null) {
            $builder->inCountry($countryId);
        }
    }
}
