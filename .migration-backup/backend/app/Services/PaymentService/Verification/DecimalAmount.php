<?php
declare(strict_types=1);

namespace App\Services\PaymentService\Verification;

/**
 * Converts provider major-unit decimals to the v1 hundredths representation.
 * Never multiply binary floating point and compare it to an integer.
 */
final class DecimalAmount
{
    public static function minor(mixed $amount): ?int
    {
        if (is_float($amount)) {
            if (!is_finite($amount)) {
                return null;
            }
            // JSON's decimal representation retains the provider number,
            // without the noise introduced by multiplying its binary value.
            $amount = json_encode($amount, JSON_PRESERVE_ZERO_FRACTION);
        } elseif (is_int($amount)) {
            $amount = (string) $amount;
        }
        if (!is_string($amount)
            || !preg_match('/^(0|[1-9][0-9]{0,12})(?:\.([0-9]{1,2}))?$/D', $amount, $matches)) {
            return null;
        }
        return (int) $matches[1] * 100 + (int) str_pad($matches[2] ?? '', 2, '0');
    }
}