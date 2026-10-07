<?php

declare(strict_types=1);

namespace Tests\Development;

use App\Console\Commands\DevelopmentDatabaseGuard;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class RuntimeDatabaseGuardTest extends TestCase
{
    private string $basePath;

    private string $databasePath;

    /** @var array<string, mixed> */
    private array $manifest;

    protected function setUp(): void
    {
        $this->basePath = sys_get_temp_dir() . '/agendaally-runtime-' . bin2hex(random_bytes(8));
        $databaseDirectory = $this->basePath . '/database/development';
        mkdir($databaseDirectory, 0700, true);
        $this->databasePath = $databaseDirectory . '/agendaally.sqlite';
        $this->manifest = [
            'schema_version' => 1,
            'demo_seed_version' => 1,
            'migration_set_sha256' => str_repeat('a', 64),
        ];
    }

    protected function tearDown(): void
    {
        if (is_file($this->databasePath)) {
            unlink($this->databasePath);
        }

        if (is_dir($this->basePath . '/database/development')) {
            rmdir($this->basePath . '/database/development');
            rmdir($this->basePath . '/database');
        }

        if (is_dir($this->basePath)) {
            rmdir($this->basePath);
        }
    }

    public function test_owned_local_sqlite_marker_allows_http_startup(): void
    {
        $pdo = new PDO('sqlite:' . $this->databasePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $pdo->exec(
            'CREATE TABLE agendaally_development_environment '
            . '(environment TEXT, database_path TEXT, schema_version INTEGER, migration_set_sha256 TEXT, demo_seed_version INTEGER DEFAULT 0)'
        );
        $statement = $pdo->prepare(
            'INSERT INTO agendaally_development_environment '
            . '(environment, database_path, schema_version, migration_set_sha256) VALUES (?, ?, ?, ?)'
        );
        $statement->execute([
            'local',
            'database/development/agendaally.sqlite',
            1,
            str_repeat('a', 64),
        ]);
        unset($pdo);

        DevelopmentDatabaseGuard::assertRuntimeDatabase(
            $this->basePath,
            'local',
            true,
            true,
            'sqlite',
            ['database' => $this->databasePath, 'url' => null],
            $this->manifest
        );

        $this->assertTrue(true);
    }

    public function test_http_startup_rejects_a_sqlite_file_without_the_ownership_marker(): void
    {
        new PDO('sqlite:' . $this->databasePath);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('requires the owned development SQLite marker');

        DevelopmentDatabaseGuard::assertRuntimeDatabase(
            $this->basePath,
            'local',
            true,
            true,
            'sqlite',
            ['database' => $this->databasePath, 'url' => null],
            $this->manifest
        );
    }

    public function test_http_startup_rejects_unapproved_environment_or_database_path(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('APP_ENV=local');

        DevelopmentDatabaseGuard::assertRuntimeDatabase(
            $this->basePath,
            'production',
            true,
            true,
            'sqlite',
            ['database' => $this->databasePath, 'url' => null],
            $this->manifest
        );
    }
}