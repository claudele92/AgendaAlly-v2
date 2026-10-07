<?php
declare(strict_types=1);

namespace App\Services\BookingService;

use App\Models\MasterDisabledTime as Rule;
use DateTimeImmutable;
use DomainException;

/**
 * Calendar occurrences, independent of working hours and the discovery window.
 * Existing custom payload: [every] or [every, weekday, ...]; weeks start Sunday,
 * as in the native calendar. Monthly anchors never overflow or clamp.
 */
final class DisabledTimeRecurrence
{
    public static function dates(Rule $rule, string $start, string $end): array
    {
        $origin = self::date((string) $rule->date);
        $from = self::date($start);
        $until = self::date($end);
        if ($rule->repeats === Rule::DONT_REPEAT) {
            return $origin >= $from && $origin <= $until ? [$origin->format('Y-m-d')] : [];
        }
        if (!in_array($rule->repeats, [Rule::DAY, Rule::WEEK, Rule::MONTH, Rule::CUSTOM], true)) {
            throw new DomainException('Unsupported disabled-time recurrence.');
        }
        if (!in_array($rule->end_type, [Rule::NEVER, Rule::DATE, Rule::AFTER], true)) {
            throw new DomainException('Unsupported disabled-time end rule.');
        }
        $limit = $rule->end_type === Rule::AFTER ? self::positive($rule->end_value) : null;
        if ($rule->end_type === Rule::DATE) {
            $until = min($until, self::date((string) $rule->end_value));
        }
        $from = max($from, $origin);
        if ($from > $until) {
            return [];
        }
        $custom = $rule->repeats === Rule::CUSTOM;
        $kind = $custom ? $rule->custom_repeat_type : $rule->repeats;
        $values = $rule->custom_repeat_value ?? [];
        $every = $custom ? self::positive($values[0] ?? null) : 1;
        if ($kind === Rule::MONTH) {
            return self::months($origin, $from, $until, $every, $limit);
        }
        if (!in_array($kind, [Rule::DAY, Rule::WEEK], true)) {
            throw new DomainException('Unsupported custom recurrence.');
        }
        $weekdays = [(int) $origin->format('w')];
        if ($custom && $kind === Rule::WEEK) {
            $names = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            $weekdays = [];
            foreach (array_slice($values, 1) as $name) {
                $day = array_search($name, $names, true);
                if ($day === false) {
                    throw new DomainException('Invalid custom recurrence weekday.');
                }
                $weekdays[] = $day;
            }
            $weekdays = array_values(array_unique($weekdays));
            if (!$weekdays) {
                throw new DomainException('Custom weekly recurrence requires weekdays.');
            }
        }
        sort($weekdays);
        $originWeekday = (int) $origin->format('w');
        $weekOrigin = $origin->modify("-{$originWeekday} days");
        $firstWeekCount = count(array_filter($weekdays, fn($d) => $d >= $originWeekday));
        $dates = [];
        // Iterate only the requested range; ordinal arithmetic includes skipped history.
        for ($day = $from; $day <= $until; $day = $day->modify('+1 day')) {
            if ($kind === Rule::DAY) {
                $offset = (int) $origin->diff($day)->days;
                if ($offset % $every !== 0) {
                    continue;
                }
                $ordinal = intdiv($offset, $every) + 1;
            } else {
                $week = intdiv((int) $weekOrigin->diff($day)->days, 7);
                $weekday = (int) $day->format('w');
                if ($week % $every !== 0 || !in_array($weekday, $weekdays, true)) {
                    continue;
                }
                $cycle = intdiv($week, $every);
                $withinWeek = count(array_filter($weekdays, fn($d) => $d <= $weekday));
                $ordinal = $cycle === 0
                    ? count(array_filter($weekdays, fn($d) => $d >= $originWeekday && $d <= $weekday))
                    : $firstWeekCount + ($cycle - 1) * count($weekdays) + $withinWeek;
            }
            if ($limit !== null && $ordinal > $limit) {
                break;
            }
            $dates[] = $day->format('Y-m-d');
        }
        return $dates;
    }

    private static function months(DateTimeImmutable $origin, DateTimeImmutable $from, DateTimeImmutable $until, int $every, ?int $limit): array
    {
        $base = $origin->modify('first day of this month');
        $totalMonths = ((int) $until->format('Y') - (int) $base->format('Y')) * 12
            + (int) $until->format('n') - (int) $base->format('n');
        $dayOfMonth = (int) $origin->format('j');
        $ordinal = 0;
        $dates = [];
        for ($offset = 0; $offset <= $totalMonths; $offset += $every) {
            $month = $base->modify("+{$offset} months");
            if ($dayOfMonth > (int) $month->format('t')) {
                continue; // No occurrence: retain the original anchor for the next month.
            }
            $day = $month->setDate((int) $month->format('Y'), (int) $month->format('n'), $dayOfMonth);
            if ($day > $until) {
                break;
            }
            ++$ordinal;
            if ($limit !== null && $ordinal > $limit) {
                break;
            }
            if ($day >= $from) {
                $dates[] = $day->format('Y-m-d');
            }
        }
        return $dates;
    }

    private static function date(string $value): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            throw new DomainException('Invalid disabled-time recurrence date.');
        }
        return $date;
    }

    private static function positive(mixed $value): int
    {
        $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($number === false) {
            throw new DomainException('Recurrence interval/count must be a positive integer.');
        }
        return $number;
    }
}