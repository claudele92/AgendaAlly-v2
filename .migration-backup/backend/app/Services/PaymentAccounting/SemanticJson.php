<?php
declare(strict_types=1);

namespace App\Services\PaymentAccounting;

/** Read-only JSON evidence comparison; never serializes or rewrites stored evidence. */
final class SemanticJson
{
    public static function equal(string $left, string $right): bool
    {
        return self::tree($left) === self::tree($right);
    }

    private static function tree(string $json): array
    {
        // Validate the complete document first. Objects are not decoded as arrays:
        // {} and [], missing and null, and numeric/string values stay distinct.
        json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        preg_match_all('/"(?:[^"\\\\]|\\\\.)*"|-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?|true|false|null|[{}\[\]:,]/s', $json, $matches);
        $position = 0;
        return self::value($matches[0], $position);
    }

    private static function value(array $tokens, int &$position): array
    {
        $token = $tokens[$position++];
        if ($token === '{') {
            $members = [];
            while ($tokens[$position] !== '}') {
                // Encode decoded keys as map keys so numeric property names cannot
                // be coerced into PHP array indices. Object order is not membership.
                $key = json_encode(json_decode($tokens[$position++], false, 512, JSON_THROW_ON_ERROR), JSON_THROW_ON_ERROR);
                $position++; // colon, validated above
                $members[$key] = self::value($tokens, $position);
                if ($tokens[$position] !== ',') break;
                $position++;
            }
            $position++;
            ksort($members, SORT_STRING);
            return ['object', $members];
        }
        if ($token === '[') {
            $items = [];
            while ($tokens[$position] !== ']') {
                $items[] = self::value($tokens, $position);
                if ($tokens[$position] !== ',') break;
                $position++;
            }
            $position++;
            return ['array', $items]; // never sort arrays
        }
        $decoded = json_decode($token, false, 512, JSON_THROW_ON_ERROR);
        if (is_int($decoded) || is_float($decoded)) {
            // Retain exact decimal value as well as decoded scalar type. Ordinary
            // json_decode rounds large integers/floats and can equate different
            // evidence. No float arithmetic is used in the comparison.
            return [get_debug_type($decoded), self::number($token)];
        }
        return [get_debug_type($decoded), $decoded];
    }

    private static function number(string $token): string
    {
        preg_match('/^(-?)(\d+)(?:\.(\d+))?(?:[eE]([+-]?\d+))?$/D', $token, $parts);
        $fraction = $parts[3] ?? '';
        $exponent = $parts[4] ?? '0';
        // Fail closed on unrepresentable exponent arithmetic, never round evidence.
        if (strlen(ltrim($exponent, '+-0')) > 6) {
            throw new \DomainException('JSON evidence exponent is outside exact comparison range.');
        }
        $power = (int)$exponent - strlen($fraction);
        $digits = ltrim($parts[2].$fraction, '0');
        if ($digits === '') return '0';
        $trimmed = rtrim($digits, '0');
        $power += strlen($digits) - strlen($trimmed);
        return $parts[1].$trimmed.'e'.$power;
    }
}