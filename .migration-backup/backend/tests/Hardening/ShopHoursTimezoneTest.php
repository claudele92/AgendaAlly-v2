<?php
declare(strict_types=1);
namespace Tests\Hardening;

use App\Support\ShopHoursTimezone;
use PHPUnit\Framework\TestCase;

final class ShopHoursTimezoneTest extends TestCase
{
    public function test_country_authority_is_unambiguous_and_not_machine_timezone(): void
    {
        self::assertSame('Africa/Douala', ShopHoursTimezone::forCountries(['cm', 'CM']));
        self::assertNull(ShopHoursTimezone::forCountries(['US']));
        self::assertNull(ShopHoursTimezone::forCountries(['CM', 'FR']));
        self::assertNull(ShopHoursTimezone::forCountries([]));
        self::assertNull(ShopHoursTimezone::forCountries([null]));
        self::assertNull(ShopHoursTimezone::forCountries(['CM', null]));
    }
}