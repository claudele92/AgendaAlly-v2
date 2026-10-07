<?php
declare(strict_types=1);

use App\Console\Commands\DevelopmentDatabaseGuard;

// Read-only preflight for the ordinary Laravel development server. This is
// not an HTTP front controller or an application/configuration replacement.
$base = $argv[1] ?? '';
if (!is_dir($base) || !is_file($base . '/vendor/autoload.php')) {
    fwrite(STDERR, "Install the Laravel dependencies first.\n");
    exit(1);
}
require $base . '/vendor/autoload.php';

try {
    DevelopmentDatabaseGuard::requireOptIn(
        (string) getenv('APP_ENV'),
        getenv('AGENDAALLY_DEVELOPMENT_DATABASE'),
        getenv('DEVELOPMENT_MODE')
    );
    $path = DevelopmentDatabaseGuard::resolveSqlitePath(
        $base,
        (string) getenv('DB_CONNECTION'),
        ['database' => getenv('DB_DATABASE'), 'url' => getenv('DB_URL') ?: null]
    );
    if (!DevelopmentDatabaseGuard::ownsDatabase(
        $path,
        $base,
        DevelopmentDatabaseGuard::reviewedManifest($base)
    )) {
        throw new RuntimeException('Bootstrap the owned development database before starting Laravel.');
    }
    fwrite(STDOUT, "Verified the owned development database; starting the original Laravel application.\n");
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . "\n");
    exit(1);
}