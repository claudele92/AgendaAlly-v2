<?php
declare(strict_types=1);

// Explicitly invoked, guarded policy-only development update. No financial seeding.
if (PHP_SAPI !== 'cli') throw new RuntimeException('CLI only.');
$backend = dirname(__DIR__, 2) . '/.migration-backup/backend';
require $backend . '/vendor/autoload.php';
$app = require $backend . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$expected = realpath($backend . '/database/development/agendaally.sqlite');
if (config('database.default') !== 'sqlite' || config('app.env') !== 'local'
    || !$expected || realpath(Illuminate\Support\Facades\DB::connection()->getDatabaseName()) !== $expected) {
    throw new RuntimeException('Only the existing normal local development SQLite database is allowed.');
}
$seeder = $app->make(Database\Seeders\DevelopmentPreviewContentSeeder::class);
$seeder->setContainer($app);
$seeder->runLegalPoliciesOnly();
echo "Guarded policy-only development update completed; custom content retained.\n";
