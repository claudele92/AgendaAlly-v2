<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class BootstrapDevelopmentDatabase extends Command
{
    protected $signature = 'development:database-bootstrap
        {--confirm-empty-sqlite : Explicitly authorize creating the owned SQLite file and applying migrations to that empty file}';

    protected $description = 'Create or migrate an explicitly opted-in, owned, isolated development SQLite database.';

    public function handle(): int
    {
        $lock = null;

        try {
            DevelopmentDatabaseGuard::requireOptIn(
                (string) config('app.env'),
                env('AGENDAALLY_DEVELOPMENT_DATABASE'),
                env('DEVELOPMENT_MODE')
            );
            $manifest = DevelopmentDatabaseGuard::reviewedManifest(base_path());
            $path = DevelopmentDatabaseGuard::resolveSqlitePath(
                base_path(),
                (string) config('database.default'),
                (array) config('database.connections.sqlite')
            );

            $directory = dirname($path);

            if (!is_dir($directory)) {
                if (!mkdir($directory, 0700, true) && !is_dir($directory)) {
                    throw new RuntimeException('Unable to create the private database/development directory.');
                }
            }

            if (is_link($directory)) {
                throw new RuntimeException('Refusing a symlinked development database directory.');
            }

            $newDatabase = !file_exists($path);

            if (file_exists($path) && !DevelopmentDatabaseGuard::ownsDatabase($path, base_path(), $manifest)) {
                throw new RuntimeException(
                    'Refusing to open or migrate an existing unowned, uninitialized, or differently-owned SQLite database. Back it up manually and select a new isolated path.'
                );
            }

            if ($newDatabase) {
                if (!$this->option('confirm-empty-sqlite')) {
                    throw new RuntimeException(
                        'No database exists. Re-run with --confirm-empty-sqlite after reviewing the historical migration inventory.'
                    );
                }

                $lock = @fopen($path, 'x+b');

                if ($lock === false) {
                    throw new RuntimeException('The development SQLite file already exists or could not be created exclusively.');
                }

                @chmod($path, 0600);

                if (!flock($lock, LOCK_EX | LOCK_NB)) {
                    throw new RuntimeException('The new SQLite database could not be exclusively locked.');
                }

                if ($this->hasApplicationTables($path)) {
                    throw new RuntimeException('The newly-created SQLite database is not empty; refusing to run historical migrations.');
                }
            } else {
                $lock = @fopen($path, 'c+b');

                if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
                    throw new RuntimeException('Unable to obtain an exclusive lock on the owned development database.');
                }

                if (!DevelopmentDatabaseGuard::ownsDatabase($path, base_path(), $manifest)) {
                    throw new RuntimeException('Development database ownership changed while acquiring its lock.');
                }
            }

            config([
                'database.default' => 'sqlite',
                'database.connections.sqlite.database' => $path,
                'database.connections.sqlite.url' => null,
            ]);
            DB::purge('sqlite');

            if (!$newDatabase) {
                if (!Schema::hasTable('migrations')) {
                    throw new RuntimeException(
                        'Refusing to replay historical migrations against an owned database whose migrations ledger is missing.'
                    );
                }

                DevelopmentDatabaseGuard::assertSafeExistingDatabaseMigrations(
                    DevelopmentDatabaseGuard::applicationMigrationNames(base_path('database/migrations')),
                    DB::table('migrations')->pluck('migration')->all(),
                    $manifest
                );
            }

            if ($newDatabase) {
                $markerResult = $this->call('migrate', [
                    '--path' => [base_path('database/development/migrations')],
                    '--realpath' => true,
                    '--force' => true,
                ]);

                if ($markerResult !== self::SUCCESS) {
                    return self::FAILURE;
                }
            }

            $migrationResult = $this->call('migrate', ['--force' => true]);

            if ($migrationResult !== self::SUCCESS) {
                return self::FAILURE;
            }

            DB::table('agendaally_development_environment')->update([
                'migration_set_sha256' => $manifest['migration_set_sha256'],
                'schema_version' => $manifest['schema_version'],
                'updated_at' => now(),
            ]);

            $this->info(sprintf(
                '%s an owned development-only SQLite database; %d reviewed application migrations are current.',
                $newDatabase ? 'Created' : 'Updated',
                $manifest['migration_count']
            ));
            $this->line('No database was dropped, reset, or re-seeded.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } finally {
            if (is_resource($lock)) {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
        }
    }

    /**
     * Inspect a freshly exclusively-created file using only PDO metadata.
     * A failed/partial prior attempt is not automatically resumed.
     */
    private function hasApplicationTables(string $path): bool
    {
        $pdo = new \PDO('sqlite:' . $path, null, null, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);
        $statement = $pdo->query(
            "SELECT name FROM sqlite_master WHERE type IN ('table', 'view') AND name NOT LIKE 'sqlite_%'"
        );

        return $statement !== false && $statement->fetchColumn() !== false;
    }
}