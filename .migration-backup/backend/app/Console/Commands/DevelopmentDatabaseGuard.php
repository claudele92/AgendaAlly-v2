<?php

declare(strict_types=1);

namespace App\Console\Commands;

use RuntimeException;

/**
 * Fail-closed rules shared by the development SQLite commands.
 *
 * This deliberately does not accept a caller-selected connection, an in-memory
 * SQLite database, SQLite URI, relative path outside database/development, or
 * any database server. Historical migrations have never been reviewed for
 * replay against a nonempty application database.
 */
final class DevelopmentDatabaseGuard
{
    /**
     * Enforce the bootstrap ownership contract before a local HTTP request can
     * connect to the selected SQLite file.
     *
     * @param array<string, mixed> $configuration
     * @param array<string, mixed> $manifest
     */
    public static function assertRuntimeDatabase(
        string $basePath,
        string $environment,
        mixed $optIn,
        mixed $developmentMode,
        string $defaultConnection,
        array $configuration,
        array $manifest
    ): void {
        self::requireOptIn($environment, $optIn, $developmentMode);

        $path = self::resolveSqlitePath($basePath, $defaultConnection, $configuration);

        if (!self::ownsDatabase($path, $basePath, $manifest)) {
            throw new RuntimeException(
                'Local HTTP startup requires the owned development SQLite marker. '
                . 'Create or update the isolated database with the reviewed development:database-bootstrap command.'
            );
        }
    }

    public static function requireOptIn(string $environment, mixed $optIn, mixed $developmentMode): void
    {
        $truthy = [true, 1, '1', 'true', 'yes', 'on'];

        if (
            $environment !== 'local'
            || !in_array($optIn, $truthy, true)
            || !in_array($developmentMode, $truthy, true)
        ) {
            throw new RuntimeException(
                'Development database commands require APP_ENV=local, DEVELOPMENT_MODE=true, and AGENDAALLY_DEVELOPMENT_DATABASE=true.'
            );
        }
    }

    /**
     * Resolve exactly one owned SQLite file, relative to the Laravel project.
     *
     * @param array<string, mixed> $configuration
     */
    public static function resolveSqlitePath(string $basePath, string $defaultConnection, array $configuration): string
    {
        if ($defaultConnection !== 'sqlite') {
            throw new RuntimeException('Development database commands support only the explicitly selected sqlite connection.');
        }

        if (($configuration['url'] ?? null) || ($configuration['database'] ?? null) === ':memory:') {
            throw new RuntimeException('Development database commands refuse SQLite URLs, in-memory databases, and remote URLs.');
        }

        $database = $configuration['database'] ?? null;

        if (!is_string($database) || $database === '' || str_starts_with($database, 'file:')) {
            throw new RuntimeException('Set DB_DATABASE to a real SQLite file under database/development/.');
        }

        $base = rtrim($basePath, DIRECTORY_SEPARATOR);
        $developmentDirectory = $base . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'development';
        $path = str_starts_with($database, DIRECTORY_SEPARATOR)
            ? $database
            : $base . DIRECTORY_SEPARATOR . $database;

        $parent = dirname($path);
        $normalizedDirectory = realpath($developmentDirectory);
        $normalizedParent = realpath($parent);

        if ($normalizedDirectory === false) {
            $normalizedDirectory = self::normalizePath($developmentDirectory);
        }

        if ($normalizedParent === false) {
            $normalizedParent = self::normalizePath($parent);
        }

        if (
            $normalizedParent !== $normalizedDirectory
            || !str_starts_with($path, DIRECTORY_SEPARATOR)
            || str_contains(basename($path), "\0")
            || preg_match('/\.sqlite(?:3)?\z/i', basename($path)) !== 1
        ) {
            throw new RuntimeException('DB_DATABASE must be a .sqlite file directly inside this project\'s database/development directory.');
        }

        if (is_link($developmentDirectory) || is_link($path) || is_link($parent)) {
            throw new RuntimeException('Development SQLite database paths and their directory may not be symlinks.');
        }

        return $normalizedDirectory . DIRECTORY_SEPARATOR . basename($path);
    }

    /**
     * @return array{count: int, sha256: string}
     */
    public static function inspectMigrationSet(string $migrationsPath): array
    {
        $files = glob(rtrim($migrationsPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.php');

        if ($files === false) {
            throw new RuntimeException('Unable to enumerate the application migration set.');
        }

        sort($files, SORT_STRING);
        $inventory = [];

        foreach ($files as $file) {
            $hash = hash_file('sha256', $file);

            if ($hash === false) {
                throw new RuntimeException('Unable to fingerprint an application migration.');
            }

            $inventory[] = basename($file) . ' ' . $hash;
        }

        return [
            'count' => count($inventory),
            'sha256' => hash('sha256', implode("\n", $inventory)),
        ];
    }

    /**
     * Fail closed if an owned database is missing a recorded legacy migration.
     *
     * This matters because the historical order-table migration can drop
     * populated order tables in its up() method. On an existing database it
     * must never be accidentally replayed just because the migrations ledger
     * was lost. New files are eligible for an existing development database
     * only after an explicit manifest review and allow-list entry.
     *
     * @param list<string> $sourceNames
     * @param list<string> $appliedNames
     * @param array<string, mixed> $manifest
     */
    public static function assertSafeExistingDatabaseMigrations(
        array $sourceNames,
        array $appliedNames,
        array $manifest
    ): void {
        $anchor = (string) ($manifest['latest_reviewed_migration'] ?? '');
        $approvedAdditions = $manifest['approved_incremental_migrations'] ?? [];

        if (
            $anchor === ''
            || !in_array($anchor, $sourceNames, true)
            || !is_array($approvedAdditions)
            || !in_array(
                '2023_12_07_064250_remigrate_orders_table',
                $sourceNames,
                true
            )
        ) {
            throw new RuntimeException('The historical database migration review manifest is invalid.');
        }

        foreach ($approvedAdditions as $addition) {
            if (
                !is_string($addition)
                || !in_array($addition, $sourceNames, true)
                || strcmp($addition, $anchor) <= 0
            ) {
                throw new RuntimeException(
                    'New migrations must be explicitly reviewed and have a new timestamp later than the reviewed baseline.'
                );
            }
        }

        $approvedAdditions = array_values(array_unique($approvedAdditions));
        $unreviewedAdditions = array_values(array_filter(
            $sourceNames,
            static fn (string $name): bool => strcmp($name, $anchor) > 0
                && !in_array($name, $approvedAdditions, true)
        ));
        $expectedApplied = array_values(array_diff($sourceNames, $approvedAdditions));
        $missingHistorical = array_values(array_diff($expectedApplied, $appliedNames));
        // The owned bootstrap runs this separately reviewed marker migration
        // before the historical application chain. Laravel records both in
        // the same ledger; allow only this exact marker, never arbitrary
        // development or unknown application migrations.
        $unknownApplied = array_values(array_diff(
            $appliedNames,
            $sourceNames,
            ['2026_10_01_000000_create_development_environment_table']
        ));

        if ($unreviewedAdditions !== [] || $missingHistorical !== [] || $unknownApplied !== []) {
            $missing = array_merge($unreviewedAdditions, $missingHistorical, $unknownApplied);
            $first = reset($missing);

            throw new RuntimeException(
                'Refusing to run historical migrations against an existing database. '
                . 'The migrations ledger is incomplete, contains an unknown version, or needs explicit review: '
                . (string) $first
            );
        }
    }

    /**
     * @return list<string>
     */
    public static function applicationMigrationNames(string $migrationsPath): array
    {
        $files = glob(rtrim($migrationsPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.php');

        if ($files === false) {
            throw new RuntimeException('Unable to enumerate the application migration set.');
        }

        sort($files, SORT_STRING);

        return array_map(
            static fn (string $file): string => substr(basename($file), 0, -4),
            $files
        );
    }

    /**
     * Check the marker without opening an existing database for writing.
     *
     * @param array<string, mixed> $manifest
     */
    public static function ownsDatabase(string $path, string $basePath, array $manifest): bool
    {
        if (!is_file($path) || is_link($path)) {
            return false;
        }

        $database = @fopen($path, 'rb');

        if ($database === false) {
            return false;
        }

        fclose($database);

        try {
            $pdo = new \PDO('sqlite:' . $path, null, null, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::SQLITE_ATTR_OPEN_FLAGS => \PDO::SQLITE_OPEN_READONLY,
            ]);
            $statement = $pdo->query(
                "SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'agendaally_development_environment'"
            );

            if ($statement === false || $statement->fetchColumn() === false) {
                return false;
            }

            $rows = $pdo->query(
                'SELECT environment, database_path, schema_version, demo_seed_version, migration_set_sha256 '
                . 'FROM agendaally_development_environment'
            )->fetchAll(\PDO::FETCH_ASSOC);
            $relativePath = substr($path, strlen(rtrim($basePath, DIRECTORY_SEPARATOR)) + 1);
            $demoSeedVersion = filter_var(
                $rows[0]['demo_seed_version'] ?? null,
                FILTER_VALIDATE_INT
            );
            $reviewedDemoSeedVersion = filter_var(
                $manifest['demo_seed_version'] ?? null,
                FILTER_VALIDATE_INT
            );
            $knownFingerprints = array_filter(array_merge(
                [(string) ($manifest['migration_set_sha256'] ?? '')],
                is_array($manifest['accepted_previous_migration_set_sha256'] ?? null)
                    ? $manifest['accepted_previous_migration_set_sha256']
                    : []
            ), static fn ($fingerprint): bool => is_string($fingerprint) && preg_match('/\A[a-f0-9]{64}\z/', $fingerprint) === 1);
            $fingerprintIsKnown = is_string($rows[0]['migration_set_sha256'] ?? null);

            if ($fingerprintIsKnown) {
                $fingerprintIsKnown = false;

                foreach ($knownFingerprints as $knownFingerprint) {
                    if (hash_equals($knownFingerprint, $rows[0]['migration_set_sha256'])) {
                        $fingerprintIsKnown = true;

                        break;
                    }
                }
            }

            return count($rows) === 1
                && ($rows[0]['environment'] ?? null) === 'local'
                && ($rows[0]['database_path'] ?? null) === str_replace('\\', '/', $relativePath)
                && (int) ($rows[0]['schema_version'] ?? PHP_INT_MAX) <= (int) $manifest['schema_version']
                && $demoSeedVersion !== false
                && $reviewedDemoSeedVersion !== false
                && $demoSeedVersion >= 0
                && $demoSeedVersion <= $reviewedDemoSeedVersion
                && $fingerprintIsKnown;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function reviewedManifest(string $basePath): array
    {
        $manifest = require rtrim($basePath, DIRECTORY_SEPARATOR) . '/database/development/manifest.php';

        if (!is_array($manifest)) {
            throw new RuntimeException('The checked-in development database manifest is invalid.');
        }

        $actual = self::inspectMigrationSet(rtrim($basePath, DIRECTORY_SEPARATOR) . '/database/migrations');

        if (
            $actual['count'] !== ($manifest['migration_count'] ?? null)
            || !hash_equals((string) ($manifest['migration_set_sha256'] ?? ''), $actual['sha256'])
        ) {
            throw new RuntimeException(
                'Historical migrations differ from the reviewed development manifest. Review the migration chain before updating database/development/manifest.php.'
            );
        }

        return $manifest;
    }

    private static function normalizePath(string $path): string
    {
        $parts = [];

        foreach (explode(DIRECTORY_SEPARATOR, str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path)) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }

            if ($part === '..') {
                array_pop($parts);

                continue;
            }

            $parts[] = $part;
        }

        return DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts);
    }
}