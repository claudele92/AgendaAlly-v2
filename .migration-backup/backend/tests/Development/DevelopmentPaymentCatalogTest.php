<?php

declare(strict_types=1);

namespace Tests\Development;

use App\Models\Payment;
use Database\Seeders\DevelopmentPaymentCatalogSeeder;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\TestCase;

/**
 * Checks payment-catalog representation against the real owned-database guard
 * and REST listing, using only a disposable SQLite database and no providers.
 */
class DevelopmentPaymentCatalogTest extends TestCase
{
    private ?\Illuminate\Foundation\Application $app = null;
    private ?string $databasePath = null;
    private array $originalEnvironment = [];

    protected function setUp(): void
    {
        parent::setUp();

        $backendPath = dirname(__DIR__, 2);
        $this->databasePath = $backendPath . '/database/development/.payment-catalog-test-'
            . bin2hex(random_bytes(8)) . '.sqlite';
        $this->setEnvironment([
            'APP_ENV' => 'local',
            'APP_DEBUG' => 'false',
            'APP_KEY' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
            'AGENDAALLY_DEVELOPMENT_DATABASE' => 'true',
            'DEVELOPMENT_MODE' => 'true',
            'PAYMENT_MODE' => 'disabled',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => $this->databasePath,
            'DATABASE_URL' => '',
            'DB_FOREIGN_KEYS' => 'true',
            'CACHE_DRIVER' => 'array',
            'SESSION_DRIVER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'MAIL_MAILER' => 'array',
        ]);

        $this->app = require $backendPath . '/bootstrap/app.php';
        $this->app->make(ConsoleKernel::class)->bootstrap();
        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite.database', $this->databasePath);
        $this->app['config']->set('database.connections.sqlite.url', null);
        $this->app['config']->set('cache.default', 'array');
        $this->app['config']->set('session.driver', 'array');
        $this->app['config']->set('queue.default', 'sync');
        $this->app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        $this->app['config']->set('development.payments.mode', 'disabled');
        DB::purge('sqlite');

        self::assertSame(
            0,
            $this->app->make(ConsoleKernel::class)->call(
                'development:database-bootstrap',
                ['--confirm-empty-sqlite' => true]
            ),
            'The guarded bootstrap must create the disposable owned SQLite database.'
        );
    }

    protected function tearDown(): void
    {
        if ($this->app !== null) {
            DB::disconnect('sqlite');
            DB::purge('sqlite');
            $this->app->flush();
        }

        if ($this->databasePath !== null) {
            foreach ([$this->databasePath, $this->databasePath . '-journal', $this->databasePath . '-wal', $this->databasePath . '-shm'] as $path) {
                if (is_file($path) && !is_link($path)) {
                    @unlink($path);
                }
            }
        }

        foreach ($this->originalEnvironment as $name => $values) {
            foreach (['server' => '_SERVER', 'env' => '_ENV'] as $key => $superglobal) {
                if ($values[$key] === null) {
                    unset($GLOBALS[$superglobal][$name]);
                } else {
                    $GLOBALS[$superglobal][$name] = $values[$key];
                }
            }

            if ($values['process'] === false) {
                putenv($name);
            } else {
                putenv($name . '=' . $values['process']);
            }
        }

        parent::tearDown();
    }

    public function test_catalog_seed_is_offline_idempotent_and_unselectable_methods_stay_out_of_checkout(): void
    {
        $seeder = new DevelopmentPaymentCatalogSeeder();
        $seeder->run();

        $disabledTags = [
            Payment::TAG_WALLET,
            Payment::TAG_ZAIN_CASH,
            Payment::TAG_PAY_TABS,
            Payment::TAG_FLUTTER_WAVE,
            Payment::TAG_PAY_STACK,
            Payment::TAG_MERCADO_PAGO,
            Payment::TAG_RAZOR_PAY,
            Payment::TAG_STRIPE,
            Payment::TAG_PAY_PAL,
            Payment::TAG_MOYA_SAR,
            Payment::TAG_MOLLIE,
            Payment::TAG_IYZICO,
            Payment::TAG_MAKSEKESKUS,
            Payment::TAG_ORANGE,
            Payment::TAG_MTN,
            Payment::TAG_PAY_FAST,
            Payment::TAG_PAYU,
        ];

        self::assertSame(18, Payment::query()->count());
        self::assertTrue(Payment::query()->where('tag', Payment::TAG_CASH)->sole()->active);

        foreach ($disabledTags as $tag) {
            $payment = Payment::query()->where('tag', $tag)->sole();
            self::assertFalse($payment->active, "The {$tag} catalog entry must remain inactive.");
            self::assertFalse($payment->sandbox, "The {$tag} entry must not imply sandbox readiness.");
        }

        $this->assertNoProviderConfigurationOrActivity();

        $seeder->run();

        self::assertSame(18, Payment::query()->count(), 'Rerunning the catalog seed must not duplicate methods.');
        foreach ($disabledTags as $tag) {
            self::assertFalse(Payment::query()->where('tag', $tag)->sole()->active);
        }
        $this->assertNoProviderConfigurationOrActivity();

        // The actual public listing enforces active=1. This proves the seeded
        // disabled rows are descriptive catalog entries, not checkout choices.
        $request = Request::create(
            '/api/v1/rest/payments?active=1',
            'GET',
            [],
            [],
            [],
            [
                'HTTP_ACCEPT' => 'application/json',
                'REMOTE_ADDR' => '127.0.0.1',
                'SERVER_NAME' => 'localhost',
                'SERVER_PORT' => '80',
            ]
        );
        $response = $this->app->make(HttpKernel::class)->handle($request);

        try {
            self::assertSame(200, $response->getStatusCode());
            $rows = data_get($response->getData(true), 'data', []);
            if (isset($rows['data'])) {
                $rows = $rows['data'];
            }
            self::assertSame(['cash'], array_column($rows, 'tag'));
        } finally {
            $this->app->make(HttpKernel::class)->terminate($request, $response);
        }
    }

    private function assertNoProviderConfigurationOrActivity(): void
    {
        foreach ([
            'payment_payloads',
            'platform_payment_configs',
            'shop_payments',
            'payment_processes',
            'transactions',
            'payouts',
        ] as $table) {
            if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                self::assertSame(0, DB::table($table)->count(), "No payment/provider activity belongs in {$table}.");
            }
        }
    }

    private function setEnvironment(array $variables): void
    {
        foreach ($variables as $name => $value) {
            $this->originalEnvironment[$name] = [
                'server' => $_SERVER[$name] ?? null,
                'env' => $_ENV[$name] ?? null,
                'process' => getenv($name),
            ];

            putenv($name . '=' . $value);
            $_SERVER[$name] = $value;
            $_ENV[$name] = $value;
        }
    }
}