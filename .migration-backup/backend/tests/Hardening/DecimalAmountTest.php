<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Services\PaymentService\Verification\DecimalAmount;
use PHPUnit\Framework\TestCase;

final class DecimalAmountTest extends TestCase
{
    public function testExactProviderDecimalsAvoidBinaryMultiplicationErrors(): void
    {
        foreach (['19.99' => 1999, '0.29' => 29, '10.25' => 1025, '0.01' => 1,
            '1.0' => 100, '1' => 100, '0.00' => 0, '999.99' => 99999] as $major => $minor) {
            self::assertSame($minor, DecimalAmount::minor((string) $major));
            self::assertSame($minor, DecimalAmount::minor((float) $major));
        }
        self::assertSame(1900, DecimalAmount::minor(19));
    }

    public function testUnrepresentableOrUntrustedValuesFailWithoutRounding(): void
    {
        foreach (['19.999', 19.999, '0.291', '1e2', '-1.00', '+1', '01.00',
            ' 19.99', '', null, false, true, [], INF, NAN,
            '999999999999999999999999.99'] as $value) {
            self::assertNull(DecimalAmount::minor($value));
        }
    }
}