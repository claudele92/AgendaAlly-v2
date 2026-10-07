<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Database\Connection;
use Closure;
use PDO;
use RuntimeException;
use WeakMap;

/**
 * Deterministic SQLite equivalents for small MySQL scalar functions used by
 * existing storefront SQL. Registration is deferred until Laravel is about to
 * execute its first query, keeping app bootstrap independent of the DB file's
 * existence and never replacing a user's database.
 */
final class PortableSqliteFunctions
{
    private const EARTH_MEAN_RADIUS_METERS = 6370986.0;

    private static ?WeakMap $registeredConnections = null;

    private static ?WeakMap $registeredPdos = null;

    public static function registerBeforeExecution(Connection $connection): void
    {
        if ($connection->getDriverName() !== 'sqlite') {
            return;
        }

        self::$registeredConnections ??= new WeakMap();

        if (isset(self::$registeredConnections[$connection])) {
            return;
        }

        self::registerPdoFactory($connection, $connection->getRawPdo(), false);
        self::registerPdoFactory($connection, $connection->getRawReadPdo(), true);

        self::$registeredConnections[$connection] = true;
    }

    public static function register(PDO $pdo): void
    {
        self::$registeredPdos ??= new WeakMap();

        if (isset(self::$registeredPdos[$pdo])) {
            return;
        }

        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'sqlite') {
            throw new RuntimeException('Portable spatial functions can only be registered on SQLite connections.');
        }

        if (!method_exists($pdo, 'sqliteCreateFunction')) {
            throw new RuntimeException('The PDO SQLite driver does not support registering portable report functions.');
        }

        $flags = defined('PDO::SQLITE_DETERMINISTIC')
            ? constant('PDO::SQLITE_DETERMINISTIC')
            : 0;

        $pdo->sqliteCreateFunction('field', self::field(...), -1, $flags);
        $pdo->sqliteCreateFunction('point', self::point(...), 2, $flags);
        $pdo->sqliteCreateFunction('st_distance_sphere', self::distanceSphere(...), 2, $flags);
        self::$registeredPdos[$pdo] = true;
    }

    private static function registerPdoFactory(
        Connection $connection,
        PDO|Closure|null $pdoOrFactory,
        bool $readConnection
    ): void {
        if ($pdoOrFactory instanceof PDO) {
            self::register($pdoOrFactory);

            return;
        }

        if (!$pdoOrFactory instanceof Closure) {
            return;
        }

        $factory = $pdoOrFactory;
        $registeredFactory = static function () use ($factory): PDO {
            $pdo = $factory();
            self::register($pdo);

            return $pdo;
        };

        if ($readConnection) {
            $connection->setReadPdo($registeredFactory);

            return;
        }

        $connection->setPdo($registeredFactory);
    }

    /**
     * MySQL FIELD() is 1-based for a match and 0 for no match.
     */
    public static function field(mixed ...$values): int
    {
        if (count($values) < 2 || $values[0] === null) {
            return 0;
        }

        $needle = array_shift($values);

        foreach ($values as $index => $value) {
            if ($value !== null && self::valuesEqual($needle, $value)) {
                return $index + 1;
            }
        }

        return 0;
    }

    /**
     * SQLite stores the point as a compact JSON pair for the distance UDF.
     */
    public static function point(mixed $longitude, mixed $latitude): ?string
    {
        if (!is_numeric($longitude) || !is_numeric($latitude)) {
            return null;
        }

        $longitude = (float) $longitude;
        $latitude = (float) $latitude;

        if (
            !is_finite($longitude)
            || !is_finite($latitude)
            || $longitude < -180
            || $longitude > 180
            || $latitude < -90
            || $latitude > 90
        ) {
            return null;
        }

        $encoded = json_encode([$longitude, $latitude], JSON_PRESERVE_ZERO_FRACTION);

        return is_string($encoded) ? $encoded : null;
    }

    /**
     * Match MySQL ST_Distance_Sphere for SRID 0 using the documented mean
     * Earth radius. Invalid/missing points produce SQL NULL, never a fake zero.
     */
    public static function distanceSphere(mixed $firstPoint, mixed $secondPoint): ?float
    {
        $first = self::decodePoint($firstPoint);
        $second = self::decodePoint($secondPoint);

        if ($first === null || $second === null) {
            return null;
        }

        [$firstLongitude, $firstLatitude] = $first;
        [$secondLongitude, $secondLatitude] = $second;

        $latitudeDelta = deg2rad($secondLatitude - $firstLatitude);
        $longitudeDelta = deg2rad($secondLongitude - $firstLongitude);
        $haversine = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($firstLatitude))
            * cos(deg2rad($secondLatitude))
            * sin($longitudeDelta / 2) ** 2;
        $haversine = min(1.0, max(0.0, $haversine));

        return 2 * self::EARTH_MEAN_RADIUS_METERS * asin(sqrt($haversine));
    }

    private static function decodePoint(mixed $point): ?array
    {
        if (!is_string($point)) {
            return null;
        }

        try {
            $coordinates = json_decode($point, true, 2, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (
            !is_array($coordinates)
            || count($coordinates) !== 2
            || !is_numeric($coordinates[0] ?? null)
            || !is_numeric($coordinates[1] ?? null)
        ) {
            return null;
        }

        $longitude = (float) $coordinates[0];
        $latitude = (float) $coordinates[1];

        if (
            !is_finite($longitude)
            || !is_finite($latitude)
            || $longitude < -180
            || $longitude > 180
            || $latitude < -90
            || $latitude > 90
        ) {
            return null;
        }

        return [$longitude, $latitude];
    }

    private static function valuesEqual(mixed $first, mixed $second): bool
    {
        if (is_numeric($first) && is_numeric($second)) {
            return (float) $first === (float) $second;
        }

        return (string) $first === (string) $second;
    }
}