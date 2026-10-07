<?php
declare(strict_types=1);

namespace App\Services\PaymentAccounting;

/** Native money enters as canonical decimal text; all new arithmetic is integer. */
final class ExactMoney
{
    public static function units(string $decimal, int $scale = 2): int
    {
        if ($scale < 0 || $scale > 8 || !preg_match('/^(0|[1-9][0-9]*)(?:\.([0-9]+))?$/D', $decimal, $match)) {
            throw new \DomainException('Noncanonical or unsupported native amount.');
        }
        $fraction = $match[2] ?? '';
        if (strlen($fraction) > $scale && trim(substr($fraction, $scale), '0') !== '') {
            throw new \DomainException('Native amount exceeds frozen scale.');
        }
        $text = ltrim($match[1].str_pad(substr($fraction, 0, $scale), $scale, '0'), '0') ?: '0';
        if (strlen($text) > 19 || (strlen($text) === 19 && strcmp($text, (string) PHP_INT_MAX) > 0)) {
            throw new \DomainException('Native amount exceeds signed BIGINT.');
        }
        return (int) $text;
    }

    public static function decimal(int $units, int $scale): string
    {
        $negative = $units < 0;
        $text = ltrim((string) $units, '-');
        if ($scale > 0) {
            $text = str_pad($text, $scale + 1, '0', STR_PAD_LEFT);
            $text = substr($text, 0, -$scale).'.'.substr($text, -$scale);
        }
        return ($negative ? '-' : '').$text;
    }

    public static function sum(array $amounts): int
    {
        $sum = 0;
        foreach ($amounts as $amount) {
            if (!is_int($amount) || $amount < 0 || $amount > PHP_INT_MAX - $sum) {
                throw new \DomainException('Invalid or overflowing exact sum.');
            }
            $sum += $amount;
        }
        return $sum;
    }

    /** Floor(total*weight/sum) without overflowing multiplication or using float. */
    public static function portion(int $total, int $weight, int $sum): int
    {
        if ($total < 0 || $weight < 0 || $sum <= 0 || $weight > $sum) {
            throw new \DomainException('Invalid proportion.');
        }
        $q = intdiv($total, $sum);
        $a = $total % $sum;
        $result = $q * $weight;
        // Binary modular multiplication: intermediate residues stay below sum.
        $quotient = 0; $remainder = 0;
        foreach (str_split(decbin($weight)) as $bit) {
            $quotient *= 2;
            if ($remainder >= $sum - $remainder) {
                $remainder -= $sum - $remainder; ++$quotient;
            } else {
                $remainder *= 2;
            }
            if ($bit === '1') {
                if ($remainder >= $sum - $a) {
                    $remainder -= $sum - $a; ++$quotient;
                } else {
                    $remainder += $a;
                }
            }
        }
        return $result + $quotient;
    }

    /** Remainder goes to the lowest stable context ID, constrained by capacity. */
    public static function partition(int $total, array $weights): array
    {
        ksort($weights, SORT_NUMERIC);
        $sum = self::sum(array_values($weights));
        if ($total < 0 || $total > $sum) {
            throw new \DomainException('Partition exceeds contribution capacity.');
        }
        $shares = [];
        foreach ($weights as $id => $weight) {
            $shares[$id] = $sum === 0 ? 0 : self::portion($total, $weight, $sum);
        }
        $remainder = $total - self::sum(array_values($shares));
        foreach ($weights as $id => $weight) {
            $add = min($remainder, $weight - $shares[$id]);
            $shares[$id] += $add; $remainder -= $add;
        }
        return $shares;
    }
}