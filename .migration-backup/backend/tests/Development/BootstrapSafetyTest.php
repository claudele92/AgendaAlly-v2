<?php

declare(strict_types=1);

namespace Tests\Development;

use App\Console\Commands\DevelopmentDatabaseGuard;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class BootstrapSafetyTest extends TestCase
{
    public function test_explicit_local_opt_in_is_mandatory(): void
    {
        DevelopmentDatabaseGuard::requireOptIn('local', 'true', true);
        DevelopmentDatabaseGuard::requireOptIn('local', true, 'yes');

        foreach ([
            ['production', 'true', true],
            ['staging', 'true', true],
            ['local', false, true],
            ['local', 'false', true],
            ['local', 'true', false],
            ['local', 'true', 'false'],
        ] as [$environment, $optIn, $developmentMode]) {
            try {
                DevelopmentDatabaseGuard::requireOptIn($environment, $optIn, $developmentMode);
                $this->fail('Unsafe environment or missing explicit opt-in was accepted.');
            } catch (RuntimeException $exception) {
                $this->assertStringContainsString('APP_ENV=local', $exception->getMessage());
            }
        }
    }

    public function test_only_owned_project_sqlite_files_are_accepted(): void
    {
        $basePath = dirname(__DIR__, 2);
        $developmentPath = $basePath . '/database/development/agendaally.sqlite';

        self::assertSame(
            $developmentPath,
            DevelopmentDatabaseGuard::resolveSqlitePath($basePath, 'sqlite', [
                'database' => 'database/development/agendaally.sqlite',
                'url' => null,
            ])
        );

        foreach ([
            ['mysql', ['database' => 'agendaally.sqlite']],
            ['sqlite', ['database' => ':memory:']],
            ['sqlite', ['database' => '../application.sqlite']],
            ['sqlite', ['database' => '/tmp/production.sqlite']],
            ['sqlite', ['database' => 'database/development.sqlite']],
            ['sqlite', ['database' => 'database/development/agendaally.sqlite', 'url' => 'sqlite::memory:']],
        ] as [$connection, $configuration]) {
            try {
                DevelopmentDatabaseGuard::resolveSqlitePath($basePath, $connection, $configuration);
                $this->fail('An unsupported, unsafe, or remote connection was accepted.');
            } catch (RuntimeException $exception) {
                $this->assertNotSame('', $exception->getMessage());
            }
        }
    }

    public function test_checked_in_manifest_covers_the_reviewed_migration_source(): void
    {
        $manifest = DevelopmentDatabaseGuard::reviewedManifest(dirname(__DIR__, 2));

        self::assertSame(213, $manifest['migration_count']);
        self::assertSame(1, $manifest['schema_version']);
        self::assertSame(1, $manifest['demo_seed_version']);
        self::assertSame(64, strlen($manifest['migration_set_sha256']));
        $migrationNames = DevelopmentDatabaseGuard::applicationMigrationNames(
            dirname(__DIR__, 2) . '/database/migrations'
        );
        self::assertContains($manifest['latest_reviewed_migration'], $migrationNames);
        DevelopmentDatabaseGuard::assertSafeExistingDatabaseMigrations(
            $migrationNames,
            [...$migrationNames, '2026_10_01_000000_create_development_environment_table'],
            $manifest
        );
    }

    public function test_owned_database_requires_supported_demo_seed_metadata(): void
    {
        $basePath = dirname(__DIR__, 2);
        $path = $basePath . '/database/development/.guard-check-' . bin2hex(random_bytes(8)) . '.sqlite';
        $relativePath = 'database/development/' . basename($path);
        $fingerprint = str_repeat('a', 64);
        $manifest = [
            'schema_version' => 1,
            'migration_set_sha256' => $fingerprint,
            'accepted_previous_migration_set_sha256' => [],
            'demo_seed_version' => 1,
        ];

        try {
            $pdo = new \PDO('sqlite:' . $path, null, null, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
            $pdo->exec(
                'CREATE TABLE agendaally_development_environment ('
                . 'environment TEXT, database_path TEXT, schema_version INTEGER, '
                . 'demo_seed_version INTEGER, migration_set_sha256 TEXT)'
            );
            $insert = $pdo->prepare(
                'INSERT INTO agendaally_development_environment '
                . '(environment, database_path, schema_version, demo_seed_version, migration_set_sha256) '
                . 'VALUES (?, ?, ?, ?, ?)'
            );
            $insert->execute(['local', $relativePath, 1, 0, $fingerprint]);
            $pdo = null;

            self::assertTrue(DevelopmentDatabaseGuard::ownsDatabase($path, $basePath, $manifest));

            $pdo = new \PDO('sqlite:' . $path, null, null, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
            $pdo->exec('UPDATE agendaally_development_environment SET demo_seed_version = 1');
            $pdo = null;
            self::assertTrue(DevelopmentDatabaseGuard::ownsDatabase($path, $basePath, $manifest));

            $pdo = new \PDO('sqlite:' . $path, null, null, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
            $pdo->exec('UPDATE agendaally_development_environment SET demo_seed_version = 2');
            $pdo = null;
            self::assertFalse(DevelopmentDatabaseGuard::ownsDatabase($path, $basePath, $manifest));
        } finally {
            if (is_file($path) && !is_link($path)) {
                @unlink($path);
            }
        }
    }
}