<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\DevelopmentDemoSeeder;
use Database\Seeders\DevelopmentPreviewContentSeeder;
use Database\Seeders\DevelopmentStoryFixtureSeeder;
use Database\Seeders\DevelopmentTranslationSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class SeedDevelopmentData extends Command
{
    protected $signature = 'development:database-seed {--content-only : Seed only English translations and CMS/footer content; do not modify payment records} {--stories-only : Seed only the guarded, shop-501 synthetic Stories fixture} {--footer-only : Configure only the owned footer copy and official social defaults}';

    protected $description = 'Seed realistic, idempotent development data in its owned SQLite database.';

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
            $relativePath = str_replace('\\', '/', substr($path, strlen(rtrim(base_path(), DIRECTORY_SEPARATOR)) + 1));

            if (!is_file($path) || is_link($path)) {
                throw new RuntimeException('Bootstrap the owned development database before seeding it.');
            }

            if (!DevelopmentDatabaseGuard::ownsDatabase($path, base_path(), $manifest)) {
                throw new RuntimeException('Refusing to seed an existing SQLite file without its valid local ownership marker.');
            }

            config([
                'database.default' => 'sqlite',
                'database.connections.sqlite.database' => $path,
                'database.connections.sqlite.url' => null,
            ]);
            DB::purge('sqlite');
            $lock = @fopen($path, 'c+b');

            if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
                throw new RuntimeException('Unable to obtain an exclusive lock on the owned development database.');
            }

            if (DB::connection('sqlite')->getDatabaseName() !== $path) {
                throw new RuntimeException('Refusing to seed a SQLite file different from the verified development path.');
            }

            if (!DevelopmentDatabaseGuard::ownsDatabase($path, base_path(), $manifest)) {
                throw new RuntimeException('Refusing to seed: development database ownership changed while acquiring its lock.');
            }

            $row = DB::connection('sqlite')->table('agendaally_development_environment')
                ->where('database_path', $relativePath)->sole();

            if (
                (int) $row->schema_version !== (int) $manifest['schema_version']
                || !hash_equals((string) $manifest['migration_set_sha256'], (string) $row->migration_set_sha256)
            ) {
                throw new RuntimeException(
                    'Refusing to seed: database ownership, reviewed migration version, and current SQLite path must all agree.'
                );
            }

            $onlyContent = (bool) $this->option('content-only');
            $onlyStories = (bool) $this->option('stories-only');
            $onlyFooter = (bool) $this->option('footer-only');

            if ((int) $onlyContent + (int) $onlyStories + (int) $onlyFooter > 1) {
                throw new RuntimeException('Choose only one of --content-only, --stories-only or --footer-only.');
            }

            if ($onlyFooter) {
                app(DevelopmentPreviewContentSeeder::class)->runFooterOnly();
                $result = self::SUCCESS;
            } elseif ($onlyStories) {
                $result = DB::transaction(function (): int {
                    $seedResult = $this->call('db:seed', [
                        '--class' => DevelopmentStoryFixtureSeeder::class,
                        '--force' => true,
                    ]);

                    if ($seedResult !== self::SUCCESS) {
                        throw new RuntimeException('The owned Stories-only fixture seed failed; its database changes were rolled back.');
                    }

                    return self::SUCCESS;
                });
            } elseif ($onlyContent) {
                $result = DB::transaction(function (): int {
                    foreach ([
                        DevelopmentTranslationSeeder::class,
                        DevelopmentPreviewContentSeeder::class,
                    ] as $seeder) {
                        $seedResult = $this->call('db:seed', [
                            '--class' => $seeder,
                            '--force' => true,
                        ]);

                        if ($seedResult !== self::SUCCESS) {
                            throw new RuntimeException(
                                'The owned translation/CMS content-only seed failed; its content changes were rolled back.'
                            );
                        }
                    }

                    return self::SUCCESS;
                });
            } else {
                $result = DB::transaction(function (): int {
                    foreach ([
                        DevelopmentDemoSeeder::class,
                        DevelopmentStoryFixtureSeeder::class,
                    ] as $seeder) {
                        $seedResult = $this->call('db:seed', [
                            '--class' => $seeder,
                            '--force' => true,
                        ]);

                        if ($seedResult !== self::SUCCESS) {
                            throw new RuntimeException(
                                'The owned development demo/Stories seed failed; its database changes were rolled back.'
                            );
                        }
                    }

                    return self::SUCCESS;
                });
            }

            if ($result !== self::SUCCESS) {
                return self::FAILURE;
            }

            if (!$onlyContent && !$onlyStories && !$onlyFooter) {
                DB::connection('sqlite')->table('agendaally_development_environment')
                    ->where('database_path', $relativePath)
                    ->update([
                        'demo_seed_version' => (int) $manifest['demo_seed_version'],
                        'updated_at' => now(),
                    ]);
            }

            $this->info($onlyFooter
                ? 'Owned footer copy and official social defaults configured; no other content or application data was seeded.'
                : ($onlyStories
                ? 'Owned synthetic Stories fixture seeded for approved shop 501 only; no other development data was changed.'
                : ($onlyContent
                ? 'Owned development English translations and CMS/footer content seeded. Payment, marketplace, account, and order records were not seeded.'
                : 'Idempotent development demo and Stories seed completed. All accounts and records are synthetic; no payment, mail, SMS, or external-provider calls are made.')));

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
}