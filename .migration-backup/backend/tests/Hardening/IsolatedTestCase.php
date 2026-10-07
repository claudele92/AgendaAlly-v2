<?php
declare(strict_types=1);

namespace Tests\Hardening;

use Illuminate\Config\Repository;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

abstract class IsolatedTestCase extends TestCase
{
    protected Application $app;
    protected Manager $database;

    protected function isolatedDatabaseConfiguration(): array
    {
        return ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''];
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Each fixture replaces "hardening" with a new, differently shaped
        // database. Laravel caches mass-assignment columns by model class,
        // not connection/schema; do not carry the previous fixture's columns.
        (new \ReflectionProperty(\Illuminate\Database\Eloquent\Model::class, 'guardableColumns'))->setValue(null, []);
        $connection = $this->isolatedDatabaseConfiguration();
        $this->app = new Application(dirname(__DIR__, 2));
        $this->app->instance('config', new Repository([
            'app' => ['env' => 'testing', 'debug' => false, 'locale' => 'en',
                'key' => 'base64:'.base64_encode(str_repeat('s', 32)), 'cipher' => 'AES-256-CBC'],
            'cache' => ['default' => 'array', 'stores' => ['array' => ['driver' => 'array']]],
            'hashing' => ['driver' => 'bcrypt', 'bcrypt' => ['rounds' => 4]],
            'database' => ['default' => 'hardening', 'connections' => [
                'hardening' => $connection,
            ]],
            'session' => ['driver' => 'array'],
        ]));
        $this->app->instance('events', new Dispatcher($this->app));
        // Tests must not write original storage/logs or boot production log
        // channels. Individual log-redaction fixtures may replace this spy.
        $this->app->instance('log', new \Psr\Log\NullLogger());
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->app);
        $this->app->register(\Illuminate\Cache\CacheServiceProvider::class);
        $this->app->register(\Illuminate\Encryption\EncryptionServiceProvider::class);
        $this->app->register(\Illuminate\Hashing\HashServiceProvider::class);
        $this->app->register(\Illuminate\Routing\RoutingServiceProvider::class);
        $this->app->register(\Illuminate\Translation\TranslationServiceProvider::class);
        $this->app->register(\Illuminate\Validation\ValidationServiceProvider::class);
        $this->database = new Manager($this->app);
        $this->database->addConnection($connection, 'hardening');
        $this->database->getDatabaseManager()->setDefaultConnection('hardening');
        $this->database->setEventDispatcher($this->app['events']);
        $this->database->setAsGlobal();
        $this->database->bootEloquent();
        $this->app->instance('db', $this->database->getDatabaseManager());
        $this->app->bind('db.schema', fn () => $this->database->schema('hardening'));
        $this->app->instance('request', \Illuminate\Http\Request::create('/api/v1', 'POST'));
        $http = new \Illuminate\Http\Client\Factory();
        $http->preventStrayRequests();
        $this->app->instance(\Illuminate\Http\Client\Factory::class, $http);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        \Illuminate\Database\Eloquent\Model::clearBootedModels();
        (new \ReflectionProperty(\Illuminate\Database\Eloquent\Model::class, 'guardableColumns'))->setValue(null, []);
        \Illuminate\Support\Carbon::setTestNow();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        if (isset($this->database)) $this->database->getDatabaseManager()->disconnect('hardening');
        parent::tearDown();
    }
}