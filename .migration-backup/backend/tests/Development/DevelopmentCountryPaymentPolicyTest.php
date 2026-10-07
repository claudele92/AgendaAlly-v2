<?php

declare(strict_types=1);

namespace Tests\Development;

use Database\Seeders\DevelopmentCountryPaymentPolicySeeder;
use Database\Seeders\Fixtures\DevelopmentCountryPaymentPolicy;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\TestCase;

/**
 * Exercises only the policy assignment fixture against a disposable in-memory
 * SQLite schema. It never opens the application or development database.
 */
class DevelopmentCountryPaymentPolicyTest extends TestCase
{
    private ?\Illuminate\Foundation\Application $app = null;
    private array $originalEnvironment = [];

    protected function setUp(): void
    {
        parent::setUp();

        $backendPath = dirname(__DIR__, 2);
        $this->setEnvironment([
            'APP_ENV' => 'local',
            'APP_KEY' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'DATABASE_URL' => '',
            'DB_URL' => '',
            'CACHE_DRIVER' => 'array',
            'SESSION_DRIVER' => 'array',
        ]);

        $this->app = require $backendPath . '/bootstrap/app.php';
        $this->app->make(ConsoleKernel::class)->bootstrap();
        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite.database', ':memory:');
        $this->app['config']->set('database.connections.sqlite.url', null);
        DB::purge('sqlite');

        Schema::create('countries', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
        });
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->string('tag')->unique();
            $table->boolean('active')->default(false);
            $table->boolean('sandbox')->default(false);
        });
        Schema::create('country_payments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('payment_id');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['country_id', 'payment_id']);
        });
    }

    protected function tearDown(): void
    {
        if ($this->app !== null) {
            DB::disconnect('sqlite');
            DB::purge('sqlite');
            $this->app->flush();
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

    public function test_assignment_matrix_is_idempotent_and_does_not_touch_other_pairs_or_catalog_status(): void
    {
        foreach (['cm', 'ng', 'gh', 'bf', 'us'] as $code) {
            DB::table('countries')->insert(['code' => $code]);
        }

        $tags = ['paypal'];
        foreach (DevelopmentCountryPaymentPolicy::assignments() as $paymentTags) {
            $tags = array_merge($tags, $paymentTags);
        }
        $tags = array_values(array_unique($tags));
        foreach ($tags as $tag) {
            DB::table('payments')->insert([
                'tag' => $tag,
                'active' => $tag === 'cash',
                'sandbox' => false,
            ]);
        }

        $cameroonId = DB::table('countries')->where('code', 'cm')->value('id');
        $usId = DB::table('countries')->where('code', 'us')->value('id');
        $cashId = DB::table('payments')->where('tag', 'cash')->value('id');
        $mtnId = DB::table('payments')->where('tag', 'mtn')->value('id');
        $paypalId = DB::table('payments')->where('tag', 'paypal')->value('id');
        $stripeId = DB::table('payments')->where('tag', 'stripe')->value('id');

        DB::table('country_payments')->insert([
            ['country_id' => $cameroonId, 'payment_id' => $cashId, 'active' => true],
            ['country_id' => $cameroonId, 'payment_id' => $mtnId, 'active' => false],
            ['country_id' => $cameroonId, 'payment_id' => $paypalId, 'active' => false],
            ['country_id' => $usId, 'payment_id' => $stripeId, 'active' => true],
        ]);

        $seeder = new DevelopmentCountryPaymentPolicySeeder();
        $first = $seeder->applyAssignments();
        $firstAssignments = DB::table('country_payments')->orderBy('id')->get()->toArray();
        $catalogAfterFirst = DB::table('payments')->orderBy('id')->get()->toArray();
        $second = $seeder->applyAssignments();

        self::assertSame(25, $first['assignments']);
        self::assertSame(4, $first['countries']);
        self::assertSame(23, $first['inserted']);
        self::assertSame(1, $first['activated']);
        self::assertSame(1, $first['unchanged']);
        self::assertSame(25, $second['assignments']);
        self::assertSame(0, $second['inserted']);
        self::assertSame(0, $second['activated']);
        self::assertSame(25, $second['unchanged']);
        self::assertSame(27, DB::table('country_payments')->count());
        self::assertEquals($firstAssignments, DB::table('country_payments')->orderBy('id')->get()->toArray());
        self::assertEquals($catalogAfterFirst, DB::table('payments')->orderBy('id')->get()->toArray());
        self::assertSame(
            0,
            DB::table('country_payments')
                ->where('country_id', $cameroonId)
                ->where('payment_id', $paypalId)
                ->value('active'),
            'An unlisted pair inside a target country remains unchanged.'
        );
        self::assertSame(
            1,
            DB::table('country_payments')
                ->where('country_id', $usId)
                ->where('payment_id', $stripeId)
                ->value('active'),
            'An assignment belonging to another country remains unchanged.'
        );
        self::assertSame(['bf', 'cm', 'gh', 'ng', 'us'], DB::table('countries')->orderBy('code')->pluck('code')->all());
    }

    private function setEnvironment(array $environment): void
    {
        foreach ($environment as $name => $value) {
            $this->originalEnvironment[$name] = [
                'server' => $_SERVER[$name] ?? null,
                'env' => $_ENV[$name] ?? null,
                'process' => getenv($name),
            ];

            $_SERVER[$name] = $value;
            $_ENV[$name] = $value;
            putenv($name . '=' . $value);
        }
    }
}