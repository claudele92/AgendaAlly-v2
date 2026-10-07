<?php

declare(strict_types=1);

namespace Tests\Development;

use App\Console\Commands\DevelopmentDatabaseGuard;
use App\Models\CountryPermission;
use App\Models\CountryInvitation;
use App\Models\Invitation;
use App\Models\ShopPermission;
use App\Models\ShopLocation;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Database\Seeders\DevelopmentDemoSeeder;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

/**
 * Exercises the original migration chain and demo data in its own disposable
 * SQLite file. It never uses RefreshDatabase/migrate:fresh and never opens an
 * existing app database.
 */
class DevelopmentDemoSeederTest extends TestCase
{
    private ?\Illuminate\Foundation\Application $app = null;
    private ?string $databasePath = null;
    private array $originalEnvironment = [];
    private array $storyMediaPresentBeforeTest = [];

    protected function setUp(): void
    {
        parent::setUp();

        $backendPath = dirname(__DIR__, 2);
        $this->databasePath = $backendPath . '/database/development/.development-test-'
            . bin2hex(random_bytes(8)) . '.sqlite';
        $this->setEnvironment([
            'APP_ENV' => 'local',
            'APP_DEBUG' => 'false',
            'APP_KEY' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
            'AGENDAALLY_DEVELOPMENT_DATABASE' => 'true',
            'DEVELOPMENT_MODE' => 'true',
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
        DB::purge('sqlite');

        $migrationExitCode = $this->app->make(ConsoleKernel::class)->call(
            'development:database-bootstrap',
            ['--confirm-empty-sqlite' => true]
        );
        self::assertSame(
            Command::SUCCESS,
            $migrationExitCode,
            'The actual guarded bootstrap must create the disposable owned SQLite file and reviewed migrations.'
        );
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

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

        foreach ([
            '1780000000-50100000-0000-4000-8000-000000000001.jpg',
            '1780000000-50100000-0000-4000-8000-000000000002.jpg',
            '1780000000-50100000-0000-4000-8000-000000000003.jpg',
        ] as $filename) {
            $path = dirname(__DIR__, 2) . '/storage/app/public/images/stories/shops/501/' . $filename;
            if (array_key_exists($filename, $this->storyMediaPresentBeforeTest)
                && $this->storyMediaPresentBeforeTest[$filename] === false
                && is_file($path)
                && !is_link($path)) {
                @unlink($path);
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

    public function test_stories_only_fixture_is_idempotent_and_leaves_every_other_table_unchanged(): void
    {
        (new DevelopmentDemoSeeder())->run();

        $mediaDirectory = dirname(__DIR__, 2) . '/storage/app/public/images/stories/shops/501';
        foreach ([
            '1780000000-50100000-0000-4000-8000-000000000001.jpg',
            '1780000000-50100000-0000-4000-8000-000000000002.jpg',
            '1780000000-50100000-0000-4000-8000-000000000003.jpg',
        ] as $filename) {
            $this->storyMediaPresentBeforeTest[$filename] = is_file($mediaDirectory . '/' . $filename);
        }

        $unrelatedBefore = $this->nonStoryTableSnapshot();
        $firstSeedAt = CarbonImmutable::parse('2026-10-02 12:00:00');
        Carbon::setTestNow($firstSeedAt);
        $this->runStoriesOnlySeed();

        $firstRows = DB::table('stories')->where('shop_id', 501)->orderBy('id')->get()->all();
        self::assertCount(3, $firstRows);
        self::assertSame(
            [\App\Models\Product::class, \App\Models\Service::class, \App\Models\Shop::class],
            collect($firstRows)->pluck('model_type')->sort()->values()->all()
        );
        self::assertSame([1], collect($firstRows)->pluck('active')->unique()->values()->all());
        self::assertSame([501], collect($firstRows)->pluck('shop_id')->unique()->map(static fn ($id): int => (int) $id)->all());
        foreach ($firstRows as $story) {
            self::assertSame([$this->storyMediaUrl((string) $story->file_urls)], json_decode($story->file_urls, true));
            self::assertSame($firstSeedAt->format('Y-m-d H:i:s'), $story->created_at);
        }

        Carbon::setTestNow($firstSeedAt->addMinutes(5));
        $this->runStoriesOnlySeed();
        $secondRows = DB::table('stories')->where('shop_id', 501)->orderBy('id')->get()->all();
        self::assertSame(
            collect($firstRows)->pluck('id')->all(),
            collect($secondRows)->pluck('id')->all(),
            'A second explicit seed must refresh the deterministic rows rather than duplicate them.'
        );
        foreach ($secondRows as $story) {
            self::assertSame($firstSeedAt->addMinutes(5)->format('Y-m-d H:i:s'), $story->created_at);
        }

        // A changed fixture row is user-owned now. A later explicit seed must
        // leave all its fields alone while refreshing only the other exact rows.
        $edited = $secondRows[0];
        $editedCreatedAt = $firstSeedAt->subDays(2)->format('Y-m-d H:i:s');
        $editedUpdatedAt = $firstSeedAt->subDays(2)->addMinutes(1)->format('Y-m-d H:i:s');
        DB::table('stories')->where('id', $edited->id)->update([
            'active' => false,
            'created_at' => $editedCreatedAt,
            'updated_at' => $editedUpdatedAt,
        ]);
        Carbon::setTestNow($firstSeedAt->addMinutes(10));
        $this->runStoriesOnlySeed();

        $afterEditedRepeat = DB::table('stories')->where('shop_id', 501)->orderBy('id')->get()->all();
        self::assertCount(3, $afterEditedRepeat);
        $preservedEdited = DB::table('stories')->where('id', $edited->id)->first();
        self::assertSame(0, (int) $preservedEdited->active);
        self::assertSame($editedCreatedAt, $preservedEdited->created_at);
        self::assertSame($editedUpdatedAt, $preservedEdited->updated_at);
        self::assertSame($unrelatedBefore, $this->nonStoryTableSnapshot());
    }

    public function test_development_seed_is_idempotent_and_builds_relational_demo_rows(): void
    {
        self::assertSame(214, DB::table('migrations')->count());
        self::assertSame(
            1,
            DB::table('migrations')
                ->where('migration', '2026_10_01_000000_create_development_environment_table')
                ->count()
        );
        self::assertSame(
            Command::SUCCESS,
            $this->app->make(ConsoleKernel::class)->call('development:database-bootstrap'),
            'Repeat bootstrap must recognize its own marker migration without replaying historical migrations.'
        );

        $seeder = new DevelopmentDemoSeeder();
        $seeder->run();
        $firstCounts = $this->fixtureCounts();
        $this->assertFullDemoContentAndPaymentCoverage();

        self::assertGreaterThan(0, $firstCounts['users']);
        self::assertGreaterThan(0, $firstCounts['shops']);
        self::assertGreaterThan(0, $firstCounts['products']);
        self::assertGreaterThan(0, $firstCounts['stocks']);
        self::assertGreaterThan(0, $firstCounts['currencies']);
        self::assertGreaterThan(0, $firstCounts['countries']);
        self::assertGreaterThan(0, $firstCounts['invitations']);
        self::assertGreaterThan(0, $firstCounts['country_invitations']);

        $this->assertLinkedDemoOrderAndBooking();
        $this->assertFullDemoDomainCoverage();
        self::assertSame('admin@agendaally.test', DB::table('users')->where('id', 103)->value('email'));
        self::assertSame('customer@agendaally.test', DB::table('users')->where('id', 102)->value('email'));
        self::assertSame(
            0,
            DB::table('invitations')
                ->join('users', 'users.id', '=', 'invitations.user_id')
                ->where('users.email', 'not like', '%@agendaally.test')
                ->count(),
            'Invitation identities come from their linked synthetic users, not a nonexistent email column.'
        );
        self::assertGreaterThan(0, DB::table('transactions')->count(), 'Offline accounting examples should be present.');
        self::assertSame(0, DB::table('transactions')->whereNotNull('payment_sys_id')->count());
        self::assertSame(0, DB::table('transactions')->whereNotNull('payment_trx_id')->count());
        self::assertGreaterThan(0, DB::table('payouts')->count(), 'A clearly pending offline payout example should be present.');
        self::assertSame(0, DB::table('payouts')->whereNotNull('payment_id')->count());
        self::assertSame(
            DB::table('transactions')->count(),
            DB::table('transactions')->where('note', 'like', 'SYNTHETIC DEVELOPMENT ONLY:%')->count(),
            'All synthetic payment/accounting rows must carry an explicit local-only note.'
        );

        if (Schema::hasTable('email_settings')) {
            self::assertSame(0, DB::table('email_settings')->count(), 'SMTP credentials must not be seeded.');
        }

        if (Schema::hasTable('sms_gateways')) {
            self::assertSame(0, DB::table('sms_gateways')->count(), 'SMS providers must remain unconfigured.');
        }

        $this->assertDemoAccountsCanAuthenticateThroughTheHttpKernel();
        // The fixture seed models a later CLI invocation in the same PHPUnit
        // process. Laravel's HTTP kernel can leave its last authenticated
        // request bound in the container, which would correctly activate
        // country-scoped model queries if we reused it for the CLI seed.
        $this->app->instance('request', Request::create('/'));
        \Illuminate\Support\Facades\Facade::clearResolvedInstance('request');
        // Sanctum checks its web-session guard before bearer authentication.
        // Forgetting guards alone retains the last simulated login in the
        // array session; a fresh CLI process has no such authenticated session.
        $this->app['session']->driver()->flush();
        \Illuminate\Support\Facades\Auth::forgetGuards();

        $firstPaymentStatusCounts = $this->paymentStatusCounts();
        $walletSnapshot = DB::table('wallets')
            ->orderBy('id')
            ->get(['id', 'uuid', 'price'])
            ->map(static fn (object $wallet): array => [
                (int) $wallet->id,
                (string) $wallet->uuid,
                (string) $wallet->price,
            ])
            ->all();
        $stockQuantitySnapshot = DB::table('stocks')
            ->orderBy('id')
            ->get(['id', 'quantity'])
            ->map(static fn (object $stock): array => [(int) $stock->id, (int) $stock->quantity])
            ->all();
        $seeder->run();

        self::assertSame($firstCounts, $this->fixtureCounts());
        self::assertSame(
            $firstPaymentStatusCounts,
            $this->paymentStatusCounts(),
            'Repeat full-demo seeding must preserve the active and inactive payment catalog counts.'
        );
        $this->assertFullDemoContentAndPaymentCoverage();
        self::assertSame(
            $walletSnapshot,
            DB::table('wallets')
                ->orderBy('id')
                ->get(['id', 'uuid', 'price'])
                ->map(static fn (object $wallet): array => [
                    (int) $wallet->id,
                    (string) $wallet->uuid,
                    (string) $wallet->price,
                ])
                ->all(),
            'Repeat seeding must preserve pre-existing wallet identities and balances.'
        );
        self::assertSame(
            $stockQuantitySnapshot,
            DB::table('stocks')
                ->orderBy('id')
                ->get(['id', 'quantity'])
                ->map(static fn (object $stock): array => [(int) $stock->id, (int) $stock->quantity])
                ->all(),
            'Repeat seeding must not decrement existing product inventory again.'
        );
        self::assertSame(214, DB::table('migrations')->count());
        self::assertSame([], DB::select('PRAGMA foreign_key_check'), 'Demo fixtures must preserve SQLite foreign-key integrity.');
        $this->assertLinkedDemoOrderAndBooking();
        $this->assertFullDemoDomainCoverage();
        self::assertSame(0, DB::table('transactions')->whereNotNull('payment_sys_id')->count());
        self::assertSame(0, DB::table('payouts')->whereNotNull('payment_id')->count());

        $walletsBeforeFailure = DB::table('wallets')->orderBy('id')->get()->toArray();
        $stocksBeforeFailure = DB::table('stocks')->orderBy('id')->get()->toArray();
        $countsBeforeFailure = $this->fixtureCounts();
        $interruptedSeeder = new class extends DevelopmentDemoSeeder {
            public function call($class, $silent = false, array $parameters = [])
            {
                if ($class === \Database\Seeders\DemoAfricaSeeder::class) {
                    throw new RuntimeException('Forced development seed interruption after legacy wallet mutations.');
                }

                return parent::call($class, $silent, $parameters);
            }
        };

        try {
            $interruptedSeeder->run();
            self::fail('The deliberate seed interruption must propagate.');
        } catch (RuntimeException $exception) {
            self::assertSame(
                'Forced development seed interruption after legacy wallet mutations.',
                $exception->getMessage()
            );
        }

        self::assertEquals($walletsBeforeFailure, DB::table('wallets')->orderBy('id')->get()->toArray());
        self::assertEquals($stocksBeforeFailure, DB::table('stocks')->orderBy('id')->get()->toArray());
        self::assertSame($countsBeforeFailure, $this->fixtureCounts());
        self::assertSame(0, DB::connection()->transactionLevel());
    }

    private function assertFullDemoDomainCoverage(): void
    {
        self::assertGreaterThanOrEqual(4, DB::table('countries')->count());
        self::assertGreaterThanOrEqual(4, DB::table('currencies')->count());
        self::assertGreaterThanOrEqual(5, DB::table('shops')->count());
        self::assertSame(501, (int) DB::table('shops')->where('user_id', 107)->value('id'));
        self::assertSame(502, (int) DB::table('shops')->where('user_id', 113)->value('id'));
        self::assertSame('master@agendaally.test', DB::table('users')->where('id', 112)->value('email'));
        self::assertSame('sellers-ng@agendaally.test', DB::table('users')->where('id', 117)->value('email'));
        foreach (['cm' => 'XAF', 'bf' => 'XOF', 'ng' => 'NGN', 'gh' => 'GHS'] as $countryCode => $currencyTitle) {
            self::assertSame(
                $currencyTitle,
                DB::table('countries')
                    ->join('currencies', 'currencies.id', '=', 'countries.currency_id')
                    ->where('countries.code', $countryCode)
                    ->value('currencies.title'),
                "Country {$countryCode} must use its authentic demo currency."
            );
        }

        self::assertGreaterThan(
            1,
            DB::table('service_masters')->distinct('shop_id')->count('shop_id'),
            'Demo specialists must be linked to services at multiple vendors/branches.'
        );
        self::assertGreaterThan(
            1,
            DB::table('products')->distinct('shop_id')->count('shop_id'),
            'Multiple real demo vendors must carry catalog products.'
        );
        self::assertGreaterThan(
            1,
            DB::table('stocks')->distinct('product_id')->count('product_id'),
            'Demo vendor product catalogs must include inventoried stock.'
        );

        foreach (\App\Models\Shop::query()->with('seller')->orderBy('id')->get() as $shop) {
            $currency = $shop->displayCurrency();

            self::assertNotNull(
                $currency,
                "Demo shop {$shop->id} must resolve a currency from its country-configured locations."
            );
            self::assertNotNull($shop->seller);
            self::assertSame(
                (int) $currency->id,
                (int) $shop->seller->currency_id,
                "Demo shop {$shop->id} seller currency must match its configured country currency."
            );
        }

        self::assertGreaterThan(
            0,
            DB::table('stock_extras')->count(),
            'A stock variant with a translated, real product option must be present.'
        );
        self::assertGreaterThan(0, DB::table('shop_working_days')->count());
        self::assertSame(
            2,
            DB::table('master_disabled_time_translations')
                ->whereIn('title', ['Synthetic lunch break', 'Synthetic personal time off'])
                ->count()
        );
        self::assertGreaterThan(0, DB::table('wallet_histories')->count());
        self::assertGreaterThan(0, DB::table('platform_fee_ledger_entries')->count());
        self::assertGreaterThan(0, DB::table('notification_user')->count());
        self::assertSame(
            ['cash'],
            DB::table('payments')->where('active', true)->orderBy('tag')->pluck('tag')->all(),
            'The local fixture enables only genuine offline cash, never an external gateway.'
        );
        self::assertGreaterThan(0, DB::table('areas')->where('active', true)->count());
        self::assertGreaterThan(0, DB::table('delivery_points')->where('active', true)->count());
        $countryGrant = DB::table('country_invitations')
            ->join('country_roles', 'country_roles.id', '=', 'country_invitations.country_role_id')
            ->join('countries', 'countries.id', '=', 'country_invitations.country_id')
            ->join('users', 'users.id', '=', 'country_invitations.user_id')
            ->where('users.email', 'country-manager@agendaally.test')
            ->select('country_roles.name as role_name', 'countries.code as country_code', 'country_invitations.status')
            ->first();

        self::assertNotNull($countryGrant, 'The Cameroon Country Manager grant must be retained.');
        self::assertSame('Country Manager', $countryGrant->role_name);
        self::assertSame('cm', $countryGrant->country_code);
        self::assertSame(CountryInvitation::ACCEPTED, (int) $countryGrant->status);

        $staff = User::query()->where('email', 'staff@agendaally.test')->firstOrFail();
        $staffInvitation = DB::table('invitations')
            ->where('user_id', $staff->id)
            ->where('shop_id', 501)
            ->first();

        self::assertNotNull($staffInvitation, 'The local staff identity must retain its Cameroon branch invitation.');
        self::assertSame('shop_manager', $staffInvitation->role);
        self::assertSame(Invitation::ACCEPTED, (int) $staffInvitation->status);
        self::assertSame('Branch Manager', DB::table('shop_roles')->where('id', $staffInvitation->shop_role_id)->value('name'));
        self::assertSame(
            2,
            DB::table('invitation_shop_locations')->where('invitation_id', $staffInvitation->id)->count(),
            'The accepted Branch Manager invitation must retain both Douala branch assignments.'
        );

        $salesByStock = DB::table('order_details')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('orders.track_id', 'like', 'AGENDAALLY-DEVELOPMENT-DELIVERED-%')
            ->where('orders.status', 'delivered')
            ->select('order_details.stock_id', DB::raw('count(*) as order_count'))
            ->groupBy('order_details.stock_id')
            ->orderByDesc('order_count')
            ->get();

        self::assertCount(3, $salesByStock);
        self::assertSame([5, 3, 2], $salesByStock->pluck('order_count')->all());
        self::assertSame(
            10,
            DB::table('orders')->where('track_id', 'like', 'AGENDAALLY-DEVELOPMENT-DELIVERED-%')->count(),
            'The dashboard sales sample must be calculated from real delivered orders, not invented product counts.'
        );
        self::assertGreaterThan(
            0,
            DB::table('stocks')->whereIn('id', $salesByStock->pluck('stock_id'))->where('quantity', '>=', 0)->count()
        );
    }

    private function assertFullDemoContentAndPaymentCoverage(): void
    {
        self::assertSame(1, DB::table('languages')->where('locale', 'en')->count());
        self::assertSame(3, DB::table('pages')->count());
        self::assertSame(3, DB::table('page_translations')->where('locale', 'en')->count());
        self::assertSame(1, DB::table('term_conditions')->count());
        self::assertSame(1, DB::table('term_condition_translations')->where('locale', 'en')->count());
        self::assertSame(1, DB::table('privacy_policies')->count());
        self::assertSame(1, DB::table('privacy_policy_translations')->where('locale', 'en')->count());
        self::assertSame(4, DB::table('faqs')->count());
        self::assertSame(4, DB::table('faq_translations')->where('locale', 'en')->count());
        self::assertSame(3, DB::table('blogs')->count());
        self::assertSame(3, DB::table('blog_translations')->where('locale', 'en')->count());
        self::assertSame(['cash'], DB::table('payments')->where('active', true)->orderBy('tag')->pluck('tag')->all());
        self::assertSame(17, DB::table('payments')->where('active', false)->count());
    }

    /**
     * @return array{active: int, inactive: int}
     */
    private function paymentStatusCounts(): array
    {
        return [
            'active' => DB::table('payments')->where('active', true)->count(),
            'inactive' => DB::table('payments')->where('active', false)->count(),
        ];
    }

    private function assertLinkedDemoOrderAndBooking(): void
    {
        $order = DB::table('orders')->where('track_id', 'AGENDAALLY-DEVELOPMENT-UNPAID-001')->first();

        self::assertNotNull($order);
        self::assertSame('new', $order->status);
        self::assertSame('customer@agendaally.test', DB::table('users')->where('id', $order->user_id)->value('email'));
        $productCurrencyId = DB::table('shop_locations')
            ->join('countries', 'countries.id', '=', 'shop_locations.country_id')
            ->where('shop_locations.shop_id', $order->shop_id)
            ->where('shop_locations.type', ShopLocation::PRODUCT)
            ->value('countries.currency_id');
        self::assertNotNull($productCurrencyId, 'The product branch must have its country currency configured.');
        self::assertSame((int) $productCurrencyId, (int) $order->currency_id);
        self::assertGreaterThan(
            0,
            DB::table('order_details')
                ->where('order_id', $order->id)
                ->whereIn('stock_id', DB::table('stocks')->select('id'))
                ->count(),
            'Synthetic order items must reference real demo stock.'
        );

        $cartItem = DB::table('cart_detail_products')->first();

        self::assertNotNull($cartItem);
        self::assertNotNull(DB::table('stocks')->where('id', $cartItem->stock_id)->value('id'));
        $cartDetail = DB::table('cart_details')->where('id', $cartItem->cart_detail_id)->first();

        self::assertNotNull($cartDetail);
        self::assertNotNull(DB::table('shops')->where('id', $cartDetail->shop_id)->value('id'));
        $userCart = DB::table('user_carts')->where('id', $cartDetail->user_cart_id)->first();

        self::assertNotNull($userCart);
        self::assertSame(
            'customer@agendaally.test',
            DB::table('users')->where('id', $userCart->user_id)->value('email')
        );

        $booking = DB::table('bookings')
            ->where('note', 'Synthetic unpaid development appointment; no payment attempted.')
            ->first();

        self::assertNotNull($booking);
        self::assertSame('new', $booking->status);
        self::assertNotNull(DB::table('users')->where('id', $booking->user_id)->value('id'));
        self::assertNotNull(DB::table('users')->where('id', $booking->master_id)->value('id'));
        self::assertNotNull(DB::table('currencies')->where('id', $booking->currency_id)->value('id'));
        $unpaidServiceMaster = DB::table('service_masters')->where('id', $booking->service_master_id)->first();
        self::assertNotNull($unpaidServiceMaster);
        self::assertSame(
            'offline_in',
            DB::table('services')->where('id', $unpaidServiceMaster->service_id)->value('type'),
            'The synthetic physical salon appointment must use a genuine at-venue service.'
        );
        $unpaidServiceCurrencyId = DB::table('shop_locations')
            ->join('countries', 'countries.id', '=', 'shop_locations.country_id')
            ->where('shop_locations.shop_id', $unpaidServiceMaster->shop_id)
            ->where('shop_locations.type', ShopLocation::SERVICE)
            ->value('countries.currency_id');
        self::assertNotNull($unpaidServiceCurrencyId);
        self::assertSame((int) $unpaidServiceCurrencyId, (int) $booking->currency_id);

        $paidBooking = DB::table('bookings')
            ->where('note', 'SYNTHETIC DEVELOPMENT ONLY: completed offline-cash appointment.')
            ->first();
        self::assertNotNull($paidBooking);
        self::assertSame('ended', $paidBooking->status);
        self::assertGreaterThan(0, (float) $paidBooking->commission_fee);
        self::assertSame(
            'customer@agendaally.test',
            User::query()->findOrFail($paidBooking->user_id)->email,
            'The completed offline booking must belong to the documented demo customer.'
        );
        $serviceMaster = DB::table('service_masters')->where('id', $paidBooking->service_master_id)->first();
        self::assertNotNull($serviceMaster);
        self::assertSame((int) $paidBooking->master_id, (int) $serviceMaster->master_id);
        $master = User::query()->findOrFail($serviceMaster->master_id);
        self::assertSame('master@agendaally.test', $master->email);
        self::assertTrue($master->hasRole('master'));
        $masterInvitation = DB::table('invitations')
            ->where('user_id', $master->id)
            ->where('shop_id', $serviceMaster->shop_id)
            ->first();
        self::assertNotNull($masterInvitation, 'The master must retain an invitation to the bookable service branch.');
        self::assertSame(Invitation::ACCEPTED, (int) $masterInvitation->status);
        self::assertSame('master', $masterInvitation->role);

        $serviceCurrencyId = DB::table('shop_locations')
            ->join('countries', 'countries.id', '=', 'shop_locations.country_id')
            ->where('shop_locations.shop_id', $serviceMaster->shop_id)
            ->where('shop_locations.type', ShopLocation::SERVICE)
            ->value('countries.currency_id');
        self::assertNotNull($serviceCurrencyId, 'The service branch must have its country currency configured.');
        self::assertSame((int) $serviceCurrencyId, (int) $paidBooking->currency_id);
        self::assertSame(
            'paid',
            DB::table('transactions')
                ->where('payable_type', \App\Models\Booking::class)
                ->where('payable_id', $paidBooking->id)
                ->whereNull('payment_sys_id')
                ->value('status')
        );
        $feeLedgerCurrencyId = DB::table('platform_fee_ledger_entries')
            ->where('transaction_id', DB::table('transactions')
                ->where('payable_type', \App\Models\Booking::class)
                ->where('payable_id', $paidBooking->id)
                ->value('id'))
            ->value('currency_id');
        self::assertSame((int) $serviceCurrencyId, (int) $feeLedgerCurrencyId);
    }

    private function assertDemoAccountsCanAuthenticateThroughTheHttpKernel(): void
    {
        $accounts = [
            'customer@agendaally.test' => 'user',
            'country-manager@agendaally.test' => 'manager',
            'admin@agendaally.test' => 'admin',
            'manager@agendaally.test' => 'manager',
            'owner@agendaally.test' => 'seller',
            'master@agendaally.test' => 'master',
            'staff@agendaally.test' => 'shop_manager',
            'branch-manager-ng@agendaally.test' => 'shop_manager',
            'finance@agendaally.test' => 'manager',
        ];
        $kernel = $this->app->make(HttpKernel::class);
        $adminToken = null;
        $tokens = [];

        foreach ($accounts as $email => $expectedRole) {
            $user = User::query()->where('email', $email)->firstOrFail();

            self::assertTrue(
                $user->hasRole($expectedRole),
                "The documented local demo identity {$email} is missing the exact {$expectedRole} role."
            );

            $request = Request::create(
                '/api/v1/auth/login',
                'POST',
                [],
                [],
                [],
                [
                    'HTTP_ACCEPT' => 'application/json',
                    'CONTENT_TYPE' => 'application/json',
                    'REMOTE_ADDR' => '127.0.0.1',
                    'SERVER_NAME' => 'localhost',
                    'SERVER_PORT' => '80',
                ],
                json_encode([
                    'email' => $email,
                    'password' => 'AgendaAlly-Dev-Only-2026!',
                ], JSON_THROW_ON_ERROR)
            );
            $response = $kernel->handle($request);

            try {
                self::assertSame(200, $response->getStatusCode(), "The local HTTP login failed for {$email}.");
                $body = $response->getData(true);
                $token = data_get($body, 'data.token');

                self::assertIsString($token);
                self::assertNotSame('', $token);
                $tokens[$email] = $token;

                if ($email === 'staff@agendaally.test') {
                    self::assertSame(
                        'shop_manager',
                        data_get($body, 'data.user.role'),
                        'The local staff login resource must expose the staff-portal role, not the generic customer role.'
                    );
                }

                if ($email === 'admin@agendaally.test') {
                    $adminToken = $token;
                }
            } finally {
                $kernel->terminate($request, $response);
                $this->app['session']->driver()->flush();
                \Illuminate\Support\Facades\Auth::forgetGuards();
            }
        }

        $this->assertNavigationContextForDevelopmentAccounts($kernel, $tokens);

        self::assertNotNull($adminToken);
        $statisticsRequest = Request::create(
            '/api/v1/dashboard/admin/statistics/products?time=subYear&perPage=10',
            'GET',
            [],
            [],
            [],
            [
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $adminToken,
                'REMOTE_ADDR' => '127.0.0.1',
                'SERVER_NAME' => 'localhost',
                'SERVER_PORT' => '80',
            ]
        );
        $statisticsResponse = $kernel->handle($statisticsRequest);

        try {
            self::assertSame(200, $statisticsResponse->getStatusCode(), 'The real top-selling API route must respond.');
            $body = $statisticsResponse->getData(true);
            $rows = data_get($body, 'data.data', data_get($body, 'data'));

            self::assertIsArray($rows);
            self::assertNotEmpty($rows, 'The API must return rows computed from delivered demo sales.');
            self::assertSame(5, data_get($rows[0], 'count'));
            self::assertCount(3, $rows, 'Delivered sales must aggregate into one row per product before pagination.');
            self::assertSame([5, 3, 2], array_column($rows, 'count'));
            self::assertCount(3, array_unique(array_column($rows, 'id')));
            self::assertCount(3, array_unique(array_column($rows, 'product_id')));
        } finally {
            $kernel->terminate($statisticsRequest, $statisticsResponse);
            \Illuminate\Support\Facades\Auth::forgetGuards();
        }
    }

    /**
     * Exercises the additive self-context route against the disposable,
     * migrated-and-seeded SQLite fixture used by this test. No canonical
     * application database or production credentials are involved.
     */
    private function assertNavigationContextForDevelopmentAccounts($kernel, array $tokens): void
    {
        [$status] = $this->requestJson($kernel, '/api/v1/dashboard/user/navigation-context');
        self::assertSame(401, $status, 'Navigation context must require the existing Sanctum auth gate.');

        foreach ($tokens as $email => $token) {
            [$status, $response] = $this->requestJson(
                $kernel,
                '/api/v1/dashboard/user/navigation-context',
                $token
            );
            self::assertSame(200, $status, "The shared profile role gate must include {$email}.");
            $data = data_get($response, 'data');
            self::assertSame((int) User::query()->where('email', $email)->value('id'), data_get($data, 'user_id'));
            self::assertIsArray(data_get($data, 'country_scope'));
            self::assertIsArray(data_get($data, 'shop_scope'));
            self::assertIsArray(data_get($data, 'branches'));
        }

        // UserSeeder's original deliveryman fixture is retained in this
        // disposable SQLite database (User 106); it is not promoted into a
        // new account or assigned a new role. The ephemeral Sanctum token
        // exercises the real shared-profile HTTP middleware without adding
        // any credential to persistent development data.
        $deliveryman = User::query()->findOrFail(106);
        self::assertTrue($deliveryman->hasRole('deliveryman'));
        $deliverymanToken = $deliveryman->createToken('navigation-context-development-test')->plainTextToken;
        [$status, $deliverymanResponse] = $this->requestJson(
            $kernel,
            '/api/v1/dashboard/user/navigation-context',
            $deliverymanToken
        );
        self::assertSame(200, $status, 'Existing deliveryman role must pass the shared-profile auth gate.');
        self::assertSame(106, data_get($deliverymanResponse, 'data.user_id'));
        self::assertSame('deliveryman', data_get($deliverymanResponse, 'data.role'));
        self::assertSame('known', data_get($deliverymanResponse, 'data.scope_status'));
        self::assertNull(data_get($deliverymanResponse, 'data.shop'));
        self::assertSame([], data_get($deliverymanResponse, 'data.country_scope.permission_keys'));
        self::assertSame([], data_get($deliverymanResponse, 'data.shop_scope.permission_keys'));
        self::assertSame([], data_get($deliverymanResponse, 'data.branches.location_ids'));
        self::assertSame([], data_get($deliverymanResponse, 'data.branches.locations'));

        $customerToken = $tokens['customer@agendaally.test'];
        [$profileStatus, $profileBefore] = $this->requestJson(
            $kernel,
            '/api/v1/dashboard/user/profile/show',
            $customerToken
        );
        self::assertSame(200, $profileStatus);

        [$status, $customerResponse] = $this->requestJson(
            $kernel,
            '/api/v1/dashboard/user/navigation-context?shop_id=501&country_id=1&user_id=107',
            $customerToken
        );
        self::assertSame(200, $status, 'The shared user-profile route accepts customer auth without new role gates.');
        $customer = data_get($customerResponse, 'data');
        self::assertSame(102, data_get($customer, 'user_id'));
        self::assertNull(data_get($customer, 'shop'), 'Client-selected shop IDs must not switch customer context.');
        self::assertNull(data_get($customer, 'country'), 'Client-selected country IDs must not switch country context.');
        self::assertSame([], data_get($customer, 'country_scope.permission_keys'));
        self::assertSame([], data_get($customer, 'shop_scope.permission_keys'));
        self::assertFalse(data_get($customer, 'country_scope.unrestricted'));
        self::assertFalse(data_get($customer, 'shop_scope.unrestricted'));

        [$status, $profileAfter] = $this->requestJson(
            $kernel,
            '/api/v1/dashboard/user/profile/show',
            $customerToken
        );
        self::assertSame(200, $status);
        self::assertSame(
            data_get($profileBefore, 'data'),
            data_get($profileAfter, 'data'),
            'The existing UserResource profile contract must be unchanged by using the new endpoint.'
        );

        [$status, $adminResponse] = $this->requestJson(
            $kernel,
            '/api/v1/dashboard/user/navigation-context?country_id=999999',
            $tokens['admin@agendaally.test']
        );
        self::assertSame(200, $status);
        $admin = data_get($adminResponse, 'data');
        self::assertTrue(data_get($admin, 'is_super_admin'));
        self::assertNull(data_get($admin, 'country'), 'A global superadmin has no request-selected country context.');
        self::assertTrue(data_get($admin, 'country_scope.unrestricted'));
        self::assertEqualsCanonicalizing(
            CountryPermission::query()->orderBy('group')->orderBy('key')->pluck('key')->all(),
            data_get($admin, 'country_scope.permission_keys')
        );

        [$status, $countryManagerResponse] = $this->requestJson(
            $kernel,
            '/api/v1/dashboard/user/navigation-context',
            $tokens['country-manager@agendaally.test']
        );
        self::assertSame(200, $status);
        $countryManager = data_get($countryManagerResponse, 'data');
        $countryManagerUser = User::query()->where('email', 'country-manager@agendaally.test')->firstOrFail();
        $countryId = CountryInvitation::query()
            ->where('user_id', $countryManagerUser->id)
            ->where('status', CountryInvitation::ACCEPTED)
            ->value('country_id');
        self::assertSame((int) $countryId, data_get($countryManager, 'country.id'));
        self::assertSame('restricted_country', data_get($countryManager, 'country_source'));
        self::assertFalse(data_get($countryManager, 'country_scope.unrestricted'));
        $expectedCountryKeys = CountryPermission::query()
            ->orderBy('group')
            ->orderBy('key')
            ->pluck('key')
            ->filter(fn (string $key) => $countryManagerUser->hasCountryPermission((int) $countryId, $key))
            ->values()
            ->all();
        self::assertSame($expectedCountryKeys, data_get($countryManager, 'country_scope.permission_keys'));

        [$status, $ownerResponse] = $this->requestJson(
            $kernel,
            '/api/v1/dashboard/user/navigation-context',
            $tokens['owner@agendaally.test']
        );
        self::assertSame(200, $status);
        $owner = data_get($ownerResponse, 'data');
        self::assertSame(501, data_get($owner, 'shop.id'));
        self::assertTrue(data_get($owner, 'shop.owner'));
        self::assertTrue(data_get($owner, 'shop_scope.unrestricted'));
        self::assertEqualsCanonicalizing(
            ShopPermission::query()->orderBy('group')->orderBy('key')->pluck('key')->all(),
            data_get($owner, 'shop_scope.permission_keys')
        );
        self::assertTrue(data_get($owner, 'branches.unrestricted'));

        [$status, $staffResponse] = $this->requestJson(
            $kernel,
            '/api/v1/dashboard/user/navigation-context',
            $tokens['staff@agendaally.test']
        );
        self::assertSame(200, $status);
        $staff = data_get($staffResponse, 'data');
        self::assertSame(501, data_get($staff, 'shop.id'));
        self::assertFalse(data_get($staff, 'shop.owner'));
        self::assertFalse(data_get($staff, 'shop_scope.unrestricted'));
        self::assertContains('products.manage', data_get($staff, 'shop_scope.permission_keys'));
        self::assertNotContains('payments.gateways.manage', data_get($staff, 'shop_scope.permission_keys'));

        $staffUser = User::query()->where('email', 'staff@agendaally.test')->firstOrFail();
        $staffInvitation = Invitation::query()->where('user_id', $staffUser->id)->firstOrFail();
        $assignedLocationIds = $staffInvitation->shopLocations()->orderBy('shop_locations.id')->pluck('shop_locations.id')
            ->map(fn ($id) => (int) $id)
            ->all();
        self::assertFalse(data_get($staff, 'branches.unrestricted'));
        self::assertSame($assignedLocationIds, data_get($staff, 'branches.location_ids'));
        self::assertSame(
            $assignedLocationIds,
            collect(data_get($staff, 'branches.locations'))->pluck('id')->all(),
            'Only locations inside the invitation-assigned branch scope may be named.'
        );

        // A staff invitation with no assigned branches is a known, empty
        // scope, never a fallback to all shop locations.
        $staffInvitation->shopLocations()->detach();
        [$status, $unassignedStaffResponse] = $this->requestJson(
            $kernel,
            '/api/v1/dashboard/user/navigation-context',
            $tokens['staff@agendaally.test']
        );
        self::assertSame(200, $status);
        self::assertFalse(data_get($unassignedStaffResponse, 'data.branches.unrestricted'));
        self::assertSame([], data_get($unassignedStaffResponse, 'data.branches.location_ids'));
        self::assertSame([], data_get($unassignedStaffResponse, 'data.branches.locations'));
        $staffInvitation->shopLocations()->sync($assignedLocationIds);

        // Pending and revoked memberships do not resolve a shop, even when
        // the caller tries to request that shop explicitly.
        foreach ([Invitation::NEW, Invitation::CANCELED] as $deniedStatus) {
            $staffInvitation->forceFill(['status' => $deniedStatus])->save();
            [$status, $deniedResponse] = $this->requestJson(
                $kernel,
                '/api/v1/dashboard/user/navigation-context?shop_id=501',
                $tokens['staff@agendaally.test']
            );
            self::assertSame(200, $status);
            self::assertNull(data_get($deniedResponse, 'data.shop'));
            self::assertSame([], data_get($deniedResponse, 'data.shop_scope.permission_keys'));
        }
        $staffInvitation->forceFill(['status' => Invitation::ACCEPTED])->save();

        [$status, $masterResponse] = $this->requestJson(
            $kernel,
            '/api/v1/dashboard/user/navigation-context',
            $tokens['master@agendaally.test']
        );
        self::assertSame(200, $status, 'Master auth must pass the same shared-profile gate.');
        $master = data_get($masterResponse, 'data');
        self::assertFalse(data_get($master, 'shop.owner', false), 'Masters never receive owner bypass in specialist context.');
        self::assertFalse(data_get($master, 'shop_scope.unrestricted'));

        self::assertSame('known', data_get($owner, 'scope_status'));
        self::assertSame('known', data_get($countryManager, 'scope_status'));
        self::assertSame('known', data_get($customer, 'scope_status'));
    }

    /**
     * @return array{0: int, 1: array}
     */
    private function requestJson($kernel, string $path, ?string $token = null): array
    {
        $server = [
            'HTTP_ACCEPT' => 'application/json',
            'REMOTE_ADDR' => '127.0.0.1',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '80',
        ];
        if ($token !== null) {
            $server['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        }

        $request = Request::create($path, 'GET', [], [], [], $server);
        $response = $kernel->handle($request);
        try {
            return [$response->getStatusCode(), $response->getData(true)];
        } finally {
            $kernel->terminate($request, $response);
            $this->app['session']->driver()->flush();
            \Illuminate\Support\Facades\Auth::forgetGuards();
        }
    }

    /**
     * @return array<string, int>
     */
    private function fixtureCounts(): array
    {
        $counts = [];

        foreach ([
            'users',
            'shops',
            'countries',
            'currencies',
            'products',
            'stocks',
            'orders',
            'order_details',
            'bookings',
            'carts',
            'user_carts',
            'cart_details',
            'cart_detail_products',
            'master_disabled_times',
            'master_disabled_time_translations',
            'wallets',
            'wallet_histories',
            'transactions',
            'platform_fee_ledger_entries',
            'payouts',
            'notification_user',
            'payments',
            'languages',
            'settings',
            'term_conditions',
            'term_condition_translations',
            'privacy_policies',
            'privacy_policy_translations',
            'pages',
            'page_translations',
            'faqs',
            'faq_translations',
            'blogs',
            'blog_translations',
            'areas',
            'delivery_points',
            'delivery_point_working_days',
            'delivery_prices',
            'reviews',
            'likes',
            'coupons',
            'translations',
            'invitations',
            'country_invitations',
            'notifications',
            'shop_working_days',
        ] as $table) {
            self::assertTrue(Schema::hasTable($table), "Expected the full original schema to include {$table}.");
            $counts[$table] = DB::table($table)->count();
        }

        ksort($counts);

        return $counts;
    }

    /**
     * Hash complete rows, not only counts, to prove the Stories-only seeder
     * does not mutate unrelated account, catalog, booking, order, or finance data.
     *
     * @return array<string, string>
     */
    private function nonStoryTableSnapshot(): array
    {
        $tables = DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' AND name != 'stories' ORDER BY name");
        $snapshot = [];

        foreach ($tables as $table) {
            $rows = DB::table($table->name)->get()
                ->map(static fn (object $row): string => json_encode((array) $row, JSON_THROW_ON_ERROR))
                ->sort()
                ->values()
                ->all();
            $snapshot[(string) $table->name] = hash('sha256', implode("\n", $rows));
        }

        return $snapshot;
    }

    private function runStoriesOnlySeed(): void
    {
        self::assertSame(
            Command::SUCCESS,
            $this->app->make(ConsoleKernel::class)->call('development:database-seed', ['--stories-only' => true]),
            'The actual guarded Stories-only command must complete successfully.'
        );
    }

    private function storyMediaUrl(string $serializedUrls): string
    {
        $urls = json_decode($serializedUrls, true);

        self::assertIsArray($urls);
        self::assertCount(1, $urls);
        self::assertStringContainsString('/storage/images/stories/shops/501/', $urls[0]);

        return $urls[0];
    }

    /**
     * Set a fully isolated Laravel environment before bootstrapping any
     * service providers. Production-mode AppServiceProvider checks therefore
     * still run exactly as authored rather than being bypassed after boot.
     *
     * @param array<string, string> $environment
     */
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