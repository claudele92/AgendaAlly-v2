<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Country;
use App\Models\User;

/**
 * Country payment policy is managed within country authority. A global
 * superadmin remains the only actor allowed to operate outside an assigned
 * country; scoped staff need the existing transactions.manage grant.
 */
final class CountryPaymentPolicy
{
    public function allows(User $user, Country $country): bool
    {
        return $user->isSuperAdmin()
            || $user->hasCountryPermission($country->id, 'transactions.manage');
    }
}