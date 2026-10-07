<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\CountryInvitation;
use App\Models\Shop;

/**
 * Resolves the acting (authenticated) user's country restriction, if any.
 * A global superadmin, and the assigned country admin's own restriction,
 * come from the country_admins row; staff invited by that admin have no
 * country_admins row of their own, so their restriction is resolved from
 * their accepted country_invitations instead — otherwise they'd fall
 * through every check here as "unrestricted" and leak every country's
 * data past the global scope.
 */
class CountryContext
{
    public static function restrictedCountryId(): ?int
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return null;
        }

        if ($user->countryAdmin) {
            return $user->countryAdmin->country_id;
        }

        return $user->countryInvitations()
            ->where('status', CountryInvitation::ACCEPTED)
            ->whereNotNull('country_role_id')
            ->value('country_id');
    }

    /**
     * The country id to operate against for the current admin request: the
     * acting user's own restricted country if they have one (an assigned
     * country admin or accepted staff), otherwise — a global superadmin,
     * who isn't tied to any single country — whatever `country_id` the
     * request names. Used by country-role/staff-invite endpoints, which
     * are inherently scoped to one country even for an unrestricted actor.
     */
    public static function activeCountryId(): ?int
    {
        $restricted = static::restrictedCountryId();

        if ($restricted !== null) {
            return $restricted;
        }

        $value = request()->input('country_id');

        return $value !== null ? (int) $value : null;
    }

    /**
     * Shop ids belonging to the acting user's restricted country, or null
     * if the user is unrestricted (no filter should be applied at all).
     */
    public static function restrictedShopIds(): ?array
    {
        $countryId = static::restrictedCountryId();

        if ($countryId === null) {
            return null;
        }

        return Shop::whereHas('locations', fn ($q) => $q->where('country_id', $countryId))
            ->pluck('id')
            ->toArray();
    }
}
