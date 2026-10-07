<?php
declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Small, allowlisted SQL-expression helpers for the application's aggregate
 * report queries. Supports every database driver enabled in config/database.php.
 */
final class PortableSql
{
    private const DATE_FORMATS = [
        '%Y' => 'year',
        '%Y-%m' => 'month',
        '%w' => 'day_of_week',
        '%Y-%m-%w' => 'weekday',
        '%Y-%m-%d %w' => 'date_weekday',
        '%Y-%m-%d' => 'date',
        '%Y-%m-%d %H:00' => 'hour',
    ];

    public static function dateFormat(string $column, string $format, ?string $driver = null): string
    {
        self::assertColumn($column);

        $kind = self::DATE_FORMATS[$format] ?? null;
        if ($kind === null) {
            throw new InvalidArgumentException('Unsupported report date format.');
        }

        $driver ??= DB::connection()->getDriverName();

        return match ($driver) {
            'mysql', 'mariadb' => "DATE_FORMAT($column, '$format')",
            'sqlite' => "strftime('$format', $column)",
            'pgsql' => self::postgresDateFormat($column, $kind),
            'sqlsrv' => self::sqlServerDateFormat($column, $kind),
            default => throw new InvalidArgumentException('Unsupported database driver for report dates.'),
        };
    }

    public static function monthName(string $column, ?string $driver = null): string
    {
        self::assertColumn($column);
        $driver ??= DB::connection()->getDriverName();

        $monthNumber = match ($driver) {
            'mysql', 'mariadb' => "MONTH($column)",
            'sqlite' => "CAST(strftime('%m', $column) AS INTEGER)",
            'pgsql' => "CAST(EXTRACT(MONTH FROM $column) AS INTEGER)",
            'sqlsrv' => "DATEPART(MONTH, $column)",
            default => throw new InvalidArgumentException('Unsupported database driver for report dates.'),
        };

        $monthCases = [];
        foreach ([
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December',
        ] as $index => $monthName) {
            $monthCases[] = 'WHEN ' . ($index + 1) . " THEN '$monthName'";
        }

        return 'CASE ' . $monthNumber . ' ' . implode(' ', $monthCases) . ' END';
    }

    public static function elapsedWholeHours(string $startColumn, string $endColumn, ?string $driver = null): string
    {
        self::assertColumn($startColumn);
        self::assertColumn($endColumn);
        $driver ??= DB::connection()->getDriverName();

        return match ($driver) {
            'mysql', 'mariadb' => "TIMESTAMPDIFF(HOUR, $startColumn, $endColumn)",
            'sqlite' => "CAST((julianday($endColumn) - julianday($startColumn)) * 24 AS INTEGER)",
            'pgsql' => "CAST(TRUNC(EXTRACT(EPOCH FROM ($endColumn - $startColumn)) / 3600) AS BIGINT)",
            'sqlsrv' => "DATEDIFF_BIG(SECOND, $startColumn, $endColumn) / 3600",
            default => throw new InvalidArgumentException('Unsupported database driver for report intervals.'),
        };
    }

    public static function sumWhenEquals(string $column, string $value, string $sumExpression): string
    {
        $condition = self::equalsCondition($column, $value);
        self::assertExpression($sumExpression);

        return "SUM(CASE WHEN $condition THEN $sumExpression ELSE 0 END)";
    }

    public static function averageWhenEquals(string $column, string $value, string $averageExpression): string
    {
        $condition = self::equalsCondition($column, $value);
        self::assertExpression($averageExpression);

        return "AVG(CASE WHEN $condition THEN $averageExpression ELSE 0 END)";
    }

    public static function averageWhenAtLeast(string $column, int $minimum): string
    {
        self::assertColumn($column);

        return "AVG(CASE WHEN $column >= $minimum THEN $column ELSE 0 END)";
    }

    public static function countWhenEquals(string $column, string $value): string
    {
        $condition = self::equalsCondition($column, $value);

        return "SUM(CASE WHEN $condition THEN 1 ELSE 0 END)";
    }

    public static function countWhenAtOrAfter(string $column, string $date): string
    {
        self::assertColumn($column);
        $date = str_replace("'", "''", $date);

        return "SUM(CASE WHEN $column >= '$date' THEN 1 ELSE 0 END)";
    }

    /**
     * Build a bound, portable equivalent of ORDER BY FIELD(column, values).
     *
     * The returned CASE preserves FIELD's ordering contract: matching values
     * receive 1-based positions and unmatched values receive 0.
     *
     * @param list<mixed> $values
     * @return array{0: string, 1: list<mixed>}
     */
    public static function orderedValues(string $column, array $values, string $direction = 'asc'): array
    {
        self::assertColumn($column);

        $direction = strtolower($direction);
        if (!in_array($direction, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('Invalid report sort direction.');
        }

        if ($values === []) {
            throw new InvalidArgumentException('A portable explicit-value ordering requires at least one value.');
        }

        foreach ($values as $value) {
            if (!is_scalar($value) && $value !== null) {
                throw new InvalidArgumentException('Invalid explicit-value sort value.');
            }
        }

        $cases = [];
        $bindings = [];

        foreach (array_values($values) as $index => $value) {
            $cases[] = 'WHEN ? THEN ' . ($index + 1);
            $bindings[] = $value;
        }

        return [
            'CASE ' . $column . ' ' . implode(' ', $cases) . ' ELSE 0 END ' . strtoupper($direction),
            $bindings,
        ];
    }

    public static function jsonNumber(string $column, string $key, ?string $driver = null): string
    {
        self::assertColumn($column);

        if (!preg_match('/\A[a-zA-Z_][a-zA-Z0-9_]*\z/D', $key)) {
            throw new InvalidArgumentException('Invalid report JSON key.');
        }

        $driver ??= DB::connection()->getDriverName();
        $path = '$.' . $key;

        return match ($driver) {
            'mysql', 'mariadb' => "CAST(JSON_UNQUOTE(JSON_EXTRACT($column, '$path')) AS DECIMAL(20, 6))",
            'sqlite' => "CAST(json_extract($column, '$path') AS REAL)",
            'pgsql' => "CAST($column->>'$key' AS DOUBLE PRECISION)",
            'sqlsrv' => "TRY_CAST(JSON_VALUE($column, '$path') AS FLOAT)",
            default => throw new InvalidArgumentException('Unsupported database driver for report JSON values.'),
        };
    }

    public static function distanceKilometers(
        string $longitudeExpression,
        string $latitudeExpression,
        float $referenceLongitude,
        float $referenceLatitude,
        ?string $driver = null
    ): string {
        self::assertGeospatialExpression($longitudeExpression);
        self::assertGeospatialExpression($latitudeExpression);

        if (
            !is_finite($referenceLongitude)
            || !is_finite($referenceLatitude)
            || $referenceLongitude < -180
            || $referenceLongitude > 180
            || $referenceLatitude < -90
            || $referenceLatitude > 90
        ) {
            throw new InvalidArgumentException('Invalid geographic coordinates.');
        }

        $driver ??= DB::connection()->getDriverName();
        $referenceLongitudeSql = sprintf('%.12F', $referenceLongitude);
        $referenceLatitudeSql = sprintf('%.12F', $referenceLatitude);

        if (in_array($driver, ['mysql', 'mariadb', 'sqlite'], true)) {
            return "ROUND(ST_Distance_Sphere("
                . "POINT($longitudeExpression, $latitudeExpression), "
                . "POINT($referenceLongitudeSql, $referenceLatitudeSql)"
                . ') / 1000, 1)';
        }

        if (!in_array($driver, ['pgsql', 'sqlsrv'], true)) {
            throw new InvalidArgumentException('Unsupported database driver for geographic distances.');
        }

        $latitudeDelta = "RADIANS(($referenceLatitudeSql) - ($latitudeExpression))";
        $longitudeDelta = "RADIANS(($referenceLongitudeSql) - ($longitudeExpression))";
        $haversine = "(POWER(SIN($latitudeDelta / 2), 2) "
            . "+ COS(RADIANS($latitudeExpression)) * COS(RADIANS($referenceLatitudeSql)) "
            . "* POWER(SIN($longitudeDelta / 2), 2))";
        $clampedHaversine = $driver === 'pgsql'
            ? "LEAST(1, GREATEST(0, $haversine))"
            : "CASE WHEN $haversine > 1 THEN 1 WHEN $haversine < 0 THEN 0 ELSE $haversine END";

        return "ROUND((2 * 6370.986 * ASIN(SQRT($clampedHaversine))), 1)";
    }

    private static function postgresDateFormat(string $column, string $kind): string
    {
        return match ($kind) {
            'year' => "TO_CHAR($column, 'YYYY')",
            'month' => "TO_CHAR($column, 'YYYY-MM')",
            'day_of_week' => "CAST(EXTRACT(DOW FROM $column) AS INTEGER)::text",
            'weekday' => "TO_CHAR($column, 'YYYY-MM-') || CAST(EXTRACT(DOW FROM $column) AS INTEGER)::text",
            'date_weekday' => "TO_CHAR($column, 'YYYY-MM-DD ') || CAST(EXTRACT(DOW FROM $column) AS INTEGER)::text",
            'date' => "TO_CHAR($column, 'YYYY-MM-DD')",
            'hour' => "TO_CHAR($column, 'YYYY-MM-DD HH24:') || '00'",
        };
    }

    private static function sqlServerDateFormat(string $column, string $kind): string
    {
        return match ($kind) {
            'year' => "FORMAT($column, 'yyyy', 'en-US')",
            'month' => "FORMAT($column, 'yyyy-MM', 'en-US')",
            'day_of_week' => "CAST(((DATEDIFF(day, '19000107', CAST($column AS date)) % 7 + 7) % 7) AS varchar(1))",
            'weekday' => "FORMAT($column, 'yyyy-MM-', 'en-US') + CAST(((DATEDIFF(day, '19000107', CAST($column AS date)) % 7 + 7) % 7) AS varchar(1))",
            'date_weekday' => "FORMAT($column, 'yyyy-MM-dd ', 'en-US') + CAST(((DATEDIFF(day, '19000107', CAST($column AS date)) % 7 + 7) % 7) AS varchar(1))",
            'date' => "FORMAT($column, 'yyyy-MM-dd', 'en-US')",
            'hour' => "FORMAT($column, 'yyyy-MM-dd HH', 'en-US') + ':00'",
        };
    }

    private static function assertColumn(string $column): void
    {
        if (!preg_match('/\A[a-zA-Z_][a-zA-Z0-9_]*(?:\.[a-zA-Z_][a-zA-Z0-9_]*)*\z/D', $column)) {
            throw new InvalidArgumentException('Invalid report column.');
        }
    }

    private static function equalsCondition(string $column, string $value): string
    {
        self::assertColumn($column);
        $value = str_replace("'", "''", $value);

        return "$column = '$value'";
    }

    private static function assertExpression(string $expression): void
    {
        if (!preg_match('/\A[a-zA-Z0-9_ .+()\-]+\z/D', $expression)) {
            throw new InvalidArgumentException('Invalid report aggregate expression.');
        }
    }

    private static function assertGeospatialExpression(string $expression): void
    {
        if (
            !preg_match('/\A[a-zA-Z0-9_ .,+()=<>\-]+\z/D', $expression)
            || str_contains($expression, '--')
        ) {
            throw new InvalidArgumentException('Invalid geographic SQL expression.');
        }
    }
}