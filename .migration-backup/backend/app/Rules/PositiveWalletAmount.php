<?php
declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Wallet direction is expressed by type, never by a signed magnitude.
 * Check the native PHP numeric representation used by balance arithmetic,
 * including exponent overflow/underflow, without changing money precision.
 */
final class PositiveWalletAmount implements ValidationRule
{
    public static function accepts(mixed $value): bool
    {
        return is_numeric($value) && is_finite((float) $value) && (float) $value > 0;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!self::accepts($value)) {
            $fail('The :attribute must be a finite amount greater than zero.');
        }
    }
}