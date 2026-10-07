<?php
declare(strict_types=1);

// Invoked only by probe-original-boot.sh in an environment-cleared process.
$base = $argv[1] ?? '';
$vendor = $argv[2] ?? '';
if (!str_starts_with($base, sys_get_temp_dir() . '/agendaally-baseline-test.')
    || !is_file($base . '/.baseline-owned')
    || !is_file($vendor)) {
    throw new RuntimeException('Refusing an unowned baseline directory.');
}
$loader = require $vendor;
$loader->addPsr4('App\\', $base . '/app');
$app = require $base . '/bootstrap/app.php';
$app->beforeBootstrapping(
    Illuminate\Foundation\Bootstrap\RegisterProviders::class,
    function ($app): void {
        // Replace all database connections before any provider can use them.
        $app['config']->set('database', [
            'default' => 'sqlite',
            'connections' => ['sqlite' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
                'foreign_key_constraints' => true,
            ]],
            'migrations' => ['table' => 'migrations'],
        ]);
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('queue.default', 'null');
        $app['config']->set('queue.connections.null', ['driver' => 'null']);
        $app['config']->set('mail.default', 'array');
        $app['config']->set('mail.mailers.array', ['transport' => 'array']);
        $app['config']->set('broadcasting.default', 'null');
        $app['config']->set('logging.default', 'stderr');
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
        $factory = new Illuminate\Http\Client\Factory();
        $factory->preventStrayRequests();
        $app->instance(Illuminate\Http\Client\Factory::class, $factory);
    }
);
try {
    // Bootstrap the original HTTP kernel, not a replacement container.
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $kernel->bootstrap();
    $db = $app['db']->connection();
    if ($db->getDatabaseName() !== ':memory:') {
        throw new RuntimeException('Database isolation lost.');
    }
    $tables = $db->select("SELECT name FROM sqlite_master WHERE type='table'");
    $routes = $app['router']->getRoutes();
    $counts = ['booking' => 0, 'cart' => 0, 'order' => 0];
    foreach ($routes as $route) {
        foreach ($counts as $domain => $_) {
            if (str_contains($route->uri(), $domain)) {
                $counts[$domain]++;
            }
        }
    }
    echo json_encode([
        'outcome' => 'booted',
        'framework' => $app->version(),
        'database' => ':memory:',
        'tables' => count($tables),
        'route_count' => count($routes),
        'domain_route_counts' => $counts,
        'requests_executed' => 0,
        'migrations_executed' => 0,
    ], JSON_PRETTY_PRINT) . PHP_EOL;
} catch (Throwable $error) {
    // Local source paths and configuration values are intentionally omitted.
    echo json_encode([
        'outcome' => 'blocked',
        'exception' => $error::class,
        'message' => $error->getMessage(),
        'requests_executed' => 0,
        'migrations_executed' => 0,
    ], JSON_PRETTY_PRINT) . PHP_EOL;
    exit(1);
}