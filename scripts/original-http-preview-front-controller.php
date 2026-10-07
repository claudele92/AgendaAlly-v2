<?php
declare(strict_types=1);

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Http\Client\Factory;

$runtime = getenv('AGENDAALLY_PREVIEW_RUNTIME');
$database = getenv('AGENDAALLY_PREVIEW_DB');
try {
    require $runtime . '/vendor/autoload.php';

    $app = require $runtime . '/bootstrap/app.php';
    $app->beforeBootstrapping(
        Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        static function ($app) use ($database): void {
            $app['config']->set('database.default', 'sqlite');
            $app['config']->set('database.connections.sqlite.database', $database);
            $app['config']->set('database.connections.sqlite.foreign_key_constraints', true);
            $app['config']->set('session.driver', 'array');
            $app['config']->set('cache.default', 'array');
            $app['config']->set('mail.default', 'array');
            $app['config']->set(
                'cors.allowed_origins',
                array_values(array_filter(explode(',', (string)getenv('AGENDAALLY_PREVIEW_CORS_ORIGINS'))))
            );
            $app['config']->set('cors.allowed_origins_patterns', []);
        }
    );

    $safeHttp = new Factory();
    $safeHttp->preventStrayRequests();
    $app->instance('http', $safeHttp);
    $app->instance(Factory::class, $safeHttp);

    $kernel = $app->make(Kernel::class);
    $request = Request::capture();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);
} catch (Throwable $exception) {
    error_log('Original HTTP preview request failed: ' . get_class($exception));
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{"message":"Original backend request failed; inspect the local runtime error log."}';
}