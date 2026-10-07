<?php
declare(strict_types=1);

use App\Console\Commands\DevelopmentDatabaseGuard;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Output\BufferedOutput;

// Two bounded seed applications. Output only aggregate hashes and public setting keys.
$base = dirname(__DIR__, 2) . '/.migration-backup/backend';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
DevelopmentDatabaseGuard::requireOptIn((string) config('app.env'), env('AGENDAALLY_DEVELOPMENT_DATABASE'), env('DEVELOPMENT_MODE'));
$manifest = DevelopmentDatabaseGuard::reviewedManifest($base);
$path = DevelopmentDatabaseGuard::resolveSqlitePath($base, (string) config('database.default'), (array) config('database.connections.sqlite'));
if (!DevelopmentDatabaseGuard::ownsDatabase($path, $base, $manifest)) {
    throw new RuntimeException('Only the owned local development database is eligible.');
}
$allowed = ['description', 'footer_text', 'instagram', 'facebook', 'linkedin', 'twitter', 'tiktok'];
$snapshot = static function () use ($allowed): array {
    $tables = DB::table('sqlite_master')->where('type', 'table')->orderBy('name')->pluck('name');
    $protected = [];
    foreach ($tables as $table) {
        if (str_starts_with($table, 'sqlite_')) continue;
        $query = DB::table($table);
        if ($table === 'settings') $query->whereNotIn('key', $allowed);
        $rows = $query->get()->map(fn ($row) => (array) $row)->all();
        usort($rows, fn ($a, $b) => strcmp(serialize($a), serialize($b)));
        $protected[$table] = hash('sha256', serialize($rows));
    }
    return $protected;
};
$footer = static fn () => DB::table('settings')->whereIn('key', $allowed)->orderBy('id')->get()->toJson();
$before = $snapshot();
$original = $footer();
$afterFirst = null;
for ($run = 1; $run <= 2; $run++) {
    $output = new BufferedOutput();
    if ($kernel->call('development:database-seed', ['--footer-only' => true], $output) !== 0) {
        throw new RuntimeException('Guarded footer-only seed failed: ' . $output->fetch());
    }
    if ($snapshot() !== $before) throw new RuntimeException('A non-footer row changed.');
    if ($run === 1) $afterFirst = $footer();
    elseif ($footer() !== $afterFirst) throw new RuntimeException('Footer seed is not row-level idempotent.');
}
if (DB::table('settings')->select('key')->groupBy('key')->havingRaw('count(*) > 1')->exists()) {
    throw new RuntimeException('Duplicate setting keys exist.');
}
echo json_encode([
    'protected_tables' => count($before),
    'protected_sha256' => hash('sha256', serialize($before)),
    'unrelated_rows_unchanged' => true,
    'footer_changed' => $original !== $afterFirst,
    'second_run_identical' => true,
    'allowed_keys' => $allowed,
], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n";