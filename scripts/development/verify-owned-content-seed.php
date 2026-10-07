<?php

declare(strict_types=1);

use App\Console\Commands\DevelopmentDatabaseGuard;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Guarded, two-pass verification of the content-only seed against the owned
 * local SQLite database. This emits fingerprints and aggregate counts only.
 */
$backendPath = dirname(__DIR__, 2) . '/.migration-backup/backend';

if (!is_file($backendPath . '/vendor/autoload.php')) {
    fwrite(STDERR, "Focused verification requires the backend's installed Laravel dependencies.\n");
    exit(1);
}

try {
    require $backendPath . '/vendor/autoload.php';
    $application = require $backendPath . '/bootstrap/app.php';
    $kernel = $application->make(ConsoleKernel::class);
    $kernel->bootstrap();

    $manifest = DevelopmentDatabaseGuard::reviewedManifest($backendPath);
    DevelopmentDatabaseGuard::requireOptIn(
        (string) config('app.env'),
        env('AGENDAALLY_DEVELOPMENT_DATABASE'),
        env('DEVELOPMENT_MODE')
    );

    $databasePath = DevelopmentDatabaseGuard::resolveSqlitePath(
        $backendPath,
        (string) config('database.default'),
        (array) config('database.connections.sqlite', [])
    );

    if (!DevelopmentDatabaseGuard::ownsDatabase($databasePath, $backendPath, $manifest)) {
        throw new RuntimeException('The selected database is not the owned local development SQLite database.');
    }

    $relativePath = str_replace(
        '\\',
        '/',
        substr($databasePath, strlen(rtrim($backendPath, DIRECTORY_SEPARATOR)) + 1)
    );
    config([
        'database.default' => 'sqlite',
        'database.connections.sqlite.database' => $databasePath,
        'database.connections.sqlite.url' => null,
    ]);
    DB::purge('sqlite');

    if (DB::connection('sqlite')->getDatabaseName() !== $databasePath) {
        throw new RuntimeException('Laravel resolved a different database than the owned local SQLite file.');
    }

    $environment = DB::connection('sqlite')->table('agendaally_development_environment')
        ->where('database_path', $relativePath)
        ->sole();

    if (
        $environment->environment !== 'local'
        || (int) $environment->schema_version !== (int) $manifest['schema_version']
        || !hash_equals(
            (string) $manifest['migration_set_sha256'],
            (string) $environment->migration_set_sha256
        )
    ) {
        throw new RuntimeException('The owned database marker does not match the reviewed local migration manifest.');
    }

    $contentTables = [
        'languages' => ['locale' => 'en'],
        'settings' => ['key' => [
            'description',
            'footer_text',
            'instagram',
            'facebook',
            'twitter',
            'linkedin',
            'customer_app_ios',
            'customer_app_android',
            'phone',
            'address',
        ]],
        'term_conditions' => [],
        'term_condition_translations' => [],
        'privacy_policies' => [],
        'privacy_policy_translations' => [],
        'pages' => [],
        'page_translations' => [],
        'faqs' => [],
        'faq_translations' => [],
        'blogs' => [],
        'blog_translations' => [],
        'translations' => [
            'locale' => 'en',
            'group' => 'web',
            'key' => [
                'masters',
                'the.best.masters',
                'new.salons',
                'mobile.card.description.1',
                'mobile.card.description',
                'add.your.favorite.masters',
                'favorite.masters',
                'best.masters',
                'best.salon.and.master',
            ],
        ],
    ];
    $runtimeTables = [
        'cache',
        'cache_locks',
        'sessions',
        'personal_access_tokens',
        'password_reset_tokens',
        'password_resets',
        'jobs',
        'job_batches',
        'failed_jobs',
        'telescope_entries',
        'telescope_entries_tags',
        'telescope_monitoring',
        'user_activities',
        'user_activity',
        'activity_log',
        'notifications',
        'notification_users',
    ];

    foreach (array_keys($contentTables) as $table) {
        if (!Schema::connection('sqlite')->hasTable($table)) {
            throw new RuntimeException('A required CMS, settings, or translation table is missing.');
        }
    }

    $protectedTableNames = array_values(array_filter(
        DB::connection('sqlite')->table('sqlite_master')
            ->where('type', 'table')
            ->orderBy('name')
            ->pluck('name')
            ->all(),
        static fn (string $table): bool => !str_starts_with($table, 'sqlite_')
            && !in_array($table, array_merge(
                array_keys($contentTables),
                $runtimeTables,
                ['migrations']
            ), true)
    ));

    if ($protectedTableNames === []) {
        throw new RuntimeException('No unrelated application tables were found to fingerprint.');
    }

    $snapshotRows = static function (string $table, array $filters = []): array {
        $query = DB::connection('sqlite')->table($table);

        foreach ($filters as $column => $value) {
            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        }

        $rows = $query->get()->map(static function ($row): array {
            $values = (array) $row;
            unset($values['updated_at']);

            return $values;
        })->all();

        usort($rows, static fn (array $left, array $right): int => strcmp(serialize($left), serialize($right)));

        return $rows;
    };

    $fingerprint = static function (array $tables, callable $rowsForTable): array {
        $tableStates = [];
        $totalRows = 0;

        foreach ($tables as $table => $filters) {
            $rows = $rowsForTable($table, $filters);
            $count = count($rows);
            $totalRows += $count;
            $tableStates[$table] = [
                'count' => $count,
                'sha256' => hash('sha256', serialize($rows)),
            ];
        }

        ksort($tableStates);

        return [
            'tables' => count($tableStates),
            'rows' => $totalRows,
            'sha256' => hash('sha256', serialize($tableStates)),
        ];
    };

    $protectedFilters = array_fill_keys($protectedTableNames, []);
    $protectedBefore = $fingerprint($protectedFilters, $snapshotRows);
    $contentAfterFirst = null;

    for ($run = 1; $run <= 2; $run++) {
        $output = new BufferedOutput();
        $exitCode = $kernel->call(
            'development:database-seed',
            ['--content-only' => true],
            $output
        );

        if ($exitCode !== Command::SUCCESS) {
            throw new RuntimeException("The guarded content-only seed did not complete on pass {$run}.");
        }

        if (!DevelopmentDatabaseGuard::ownsDatabase($databasePath, $backendPath, $manifest)) {
            throw new RuntimeException('Database ownership verification failed after content seeding.');
        }

        $protectedAfter = $fingerprint($protectedFilters, $snapshotRows);
        if ($protectedAfter !== $protectedBefore) {
            throw new RuntimeException("Unrelated database data changed during content-only seed pass {$run}.");
        }

        $content = $fingerprint($contentTables, $snapshotRows);

        if ($run === 1) {
            $contentAfterFirst = $content;
            continue;
        }

        if ($content !== $contentAfterFirst) {
            throw new RuntimeException('CMS/settings/English translation content was not stable across the two seed passes.');
        }
    }

    echo json_encode([
        'owned_local_sqlite_guard' => 'passed',
        'content_seed_runs' => 2,
        'protected_table_count' => $protectedBefore['tables'],
        'protected_row_count' => $protectedBefore['rows'],
        'protected_data_unchanged' => true,
        'protected_sha256' => $protectedBefore['sha256'],
        'content_table_count' => $contentAfterFirst['tables'],
        'content_row_count_after_each_run' => $contentAfterFirst['rows'],
        'content_idempotent_after_updated_at_ignored' => true,
        'content_sha256_after_first_and_second_run' => $contentAfterFirst['sha256'],
    ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT) . PHP_EOL;
} catch (Throwable) {
    // Deliberately avoid printing exception messages, SQL, row values, or paths.
    fwrite(STDERR, "Focused content-seed verification stopped safely; inspect the guard or seed status without exposing database values.\n");
    exit(1);
}