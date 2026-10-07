<?php
declare(strict_types=1);

namespace App\Support;

use DateTimeZone;

final class ShopHoursTimezone
{
    /** Never guess a zone for a multi-zone country or ambiguous branches. */
    public static function forCountries(array $codes): ?string
    {
        $codes = array_unique(array_map(fn ($code) => strtoupper(trim((string) $code)), $codes));
        if (count($codes) !== 1 || !preg_match('/^[A-Z]{2}$/', reset($codes))) {
            return null;
        }
        $zones = DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, reset($codes));
        return count($zones) === 1 ? $zones[0] : null;
    }
}