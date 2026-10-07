<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Http\Controllers\API\v1\Dashboard\Payment\TransactionController;
use App\Http\Middleware\SanctumCheck;
use App\Models\Booking;
use App\Models\Invitation;
use App\Models\Order;
use App\Models\Shop;
use App\Models\Transaction;
use App\Models\User;
use App\Observers\TransactionObserver;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Providers\FormRequestServiceProvider;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Schema;
use Mockery;
use ReflectionClass;
use Spatie\Permission\Models\Role;

abstract class PaymentStatusFixture extends IsolatedTestCase
{
    protected int $transactionUpdateEvents = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app['config']->set('auth.defaults.guard', 'sanctum');
        $this->app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        // IsolatedTestCase deliberately never boots the native application.
        // Enable only FormRequest's route-resolution/validation callbacks.
        (new FormRequestServiceProvider($this->app))->boot();
        $this->createPaymentSchema();
        $this->seedPaymentFixtures();
        $this->authenticate(100, 'seller');
        Transaction::observe(TransactionObserver::class);
        Transaction::updated(function (): void {
            ++$this->transactionUpdateEvents;
        });

        // Only skip unrelated constructor lookups (default language/currency).
        // The real action, authentication middleware and FormRequest run.
        $controller = (new ReflectionClass(TransactionController::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty($controller, 'language'))->setValue($controller, 'en');
        $this->app->instance(TransactionController::class, $controller);
        $this->app['router']->put(
            '/api/v1/payments/{type}/{id}/transactions',
            [TransactionController::class, 'updateStatus']
        )->middleware(SanctumCheck::class);
    }

    protected function authenticate(?int $id, string $role = 'seller', ?int $country = null): void
    {
        $user = $id === null ? null : (new User())->forceFill([
            'id' => $id, 'email' => "fixture-{$id}@example.test", 'lang' => 'en',
        ]);
        $user?->setRelation('roles', collect([(new Role())->forceFill(['name' => $role])]));
        // A loaded neutral country authority avoids unrelated country fixture
        // discovery; a non-null country tests the real existing global scopes.
        $user?->setRelation('countryAdmin', (object) ['country_id' => $country]);
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn($id);
        $guard->shouldReceive('check')->andReturn($id !== null);
        $auth = Mockery::mock(Factory::class);
        $auth->shouldReceive('guard')->with('sanctum')->andReturn($guard);
        $this->app->instance('auth', $auth);
        Facade::clearResolvedInstance('auth');
    }

    protected function dispatchStatus(string $type, int $id, array $input): \Symfony\Component\HttpFoundation\Response
    {
        $request = Request::create("/api/v1/payments/{$type}/{$id}/transactions", 'PUT', $input);
        $request->headers->set('Accept', 'application/json');
        $this->app->instance('request', $request);
        Facade::clearResolvedInstance('request');
        try {
            return $this->app['router']->dispatch($request);
        } catch (HttpResponseException $exception) {
            return $exception->getResponse();
        }
    }

    /** Every table in the isolated database, not just the transaction row. */
    protected function fingerprint(): array
    {
        $pdo = $this->database->getConnection()->getPdo();
        $result = [];
        foreach ($pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(\PDO::FETCH_COLUMN) as $table) {
            $quoted = '"'.str_replace('"', '""', $table).'"';
            $rows = $pdo->query("SELECT * FROM {$quoted}")->fetchAll(\PDO::FETCH_ASSOC);
            $encoded = array_map(
                fn ($row) => json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR),
                $rows
            );
            sort($encoded, SORT_STRING);
            $result[$table] = ['count' => count($rows), 'sha256' => hash('sha256', implode("\n", $encoded))];
        }
        $result['_transaction_update_events'] = $this->transactionUpdateEvents;
        return $result;
    }

    private function createPaymentSchema(): void
    {
        Schema::create('translations', function (Blueprint $t): void {
            $t->increments('id'); $t->string('locale'); $t->string('key'); $t->text('value');
        });
        Schema::create('users', function (Blueprint $t): void {
            $t->increments('id');
        });
        Schema::create('shops', function (Blueprint $t): void {
            $t->increments('id'); $t->unsignedInteger('user_id')->nullable();
        });
        Schema::create('shop_locations', function (Blueprint $t): void {
            $t->increments('id'); $t->unsignedInteger('shop_id'); $t->unsignedInteger('country_id');
        });
        Schema::create('services', function (Blueprint $t): void {
            $t->increments('id'); $t->unsignedInteger('shop_id');
        });
        foreach (['orders', 'bookings'] as $name) {
            Schema::create($name, function (Blueprint $t) use ($name): void {
                $t->increments('id'); $t->unsignedInteger('shop_id')->nullable();
                $t->unsignedInteger('user_id')->nullable(); $t->unsignedInteger('currency_id')->default(1);
                $t->decimal('total_price')->default(40); $t->decimal('service_fee')->default(5);
                $t->decimal('commission_fee')->default(2); $t->decimal('rate')->default(1);
                if ($name === 'bookings') {
                    $t->unsignedInteger('service_id')->nullable();
                    $t->unsignedInteger('parent_id')->nullable();
                    $t->boolean('collect_via_platform')->default(true);
                }
                $t->timestamps();
            });
        }
        Schema::create('payments', function (Blueprint $t): void {
            $t->increments('id'); $t->string('tag'); $t->boolean('active')->default(true);
        });
        Schema::create('transactions', function (Blueprint $t): void {
            $t->increments('id'); $t->string('payable_type'); $t->unsignedInteger('payable_id');
            $t->unsignedInteger('payment_sys_id'); $t->unsignedInteger('parent_id')->nullable();
            $t->string('status'); $t->text('note')->nullable(); $t->timestamps();
        });
        Schema::create('payment_process', function (Blueprint $t): void {
            $t->string('id')->primary(); $t->json('data')->nullable(); $t->timestamps();
        });
        Schema::create('platform_fee_ledger_entries', function (Blueprint $t): void {
            $t->increments('id'); $t->unsignedInteger('transaction_id'); $t->string('entry_type');
            $t->string('payable_type'); $t->unsignedInteger('payable_id'); $t->unsignedInteger('shop_id');
            $t->unsignedInteger('payment_id'); $t->unsignedInteger('currency_id');
            $t->decimal('amount'); $t->string('status'); $t->timestamps();
            $t->unique(['transaction_id', 'entry_type']);
        });
        Schema::create('shop_roles', function (Blueprint $t): void {
            $t->increments('id'); $t->unsignedInteger('shop_id'); $t->string('name');
        });
        Schema::create('shop_permissions', function (Blueprint $t): void {
            $t->increments('id'); $t->string('key');
        });
        Schema::create('shop_role_permissions', function (Blueprint $t): void {
            $t->unsignedInteger('shop_role_id'); $t->unsignedInteger('shop_permission_id');
        });
        Schema::create('invitations', function (Blueprint $t): void {
            $t->increments('id'); $t->unsignedInteger('user_id'); $t->unsignedInteger('shop_id');
            $t->unsignedInteger('shop_role_id')->nullable(); $t->integer('status');
        });
        foreach (['shop_subscriptions', 'shop_ads_packages', 'member_ships', 'gift_carts'] as $name) {
            Schema::create($name, function (Blueprint $t): void {
                $t->increments('id'); $t->unsignedInteger('shop_id')->nullable(); $t->timestamps();
            });
        }
        foreach (['user_member_ships' => 'member_ship_id', 'user_gift_carts' => 'gift_cart_id'] as $name => $key) {
            Schema::create($name, function (Blueprint $t) use ($key): void {
                $t->increments('id'); $t->unsignedInteger($key)->nullable(); $t->timestamps();
            });
        }
        foreach (['wallets', 'parcel_orders'] as $name) {
            Schema::create($name, function (Blueprint $t): void {
                $t->increments('id'); $t->unsignedInteger('user_id')->nullable(); $t->timestamps();
                if ($t->getTable() === 'wallets') {
                    $t->decimal('price')->default(13.25);
                }
            });
        }
        Schema::create('order_refunds', function (Blueprint $t): void {
            $t->increments('id'); $t->unsignedInteger('order_id'); $t->string('status');
        });
        // Positive observer tests use the actual ledger. Denial fingerprints
        // also protect representative vendor/wallet/payout financial records.
        foreach (['wallet_histories', 'payouts', 'vendor_balances'] as $name) {
            Schema::create($name, function (Blueprint $t): void {
                $t->increments('id'); $t->decimal('amount');
            });
        }
    }

    private function seedPaymentFixtures(): void
    {
        $db = $this->database;
        $db->table('users')->insert(array_map(fn ($id) => ['id' => $id], [100, 200, 300, 301, 302, 303, 999]));
        $db->table('shops')->insert([['id' => 1, 'user_id' => 100], ['id' => 2, 'user_id' => 200]]);
        $db->table('shop_locations')->insert([
            ['id' => 1, 'shop_id' => 1, 'country_id' => 10],
            ['id' => 2, 'shop_id' => 2, 'country_id' => 20],
        ]);
        $db->table('services')->insert([['id' => 101, 'shop_id' => 1], ['id' => 102, 'shop_id' => 2]]);
        $db->table('orders')->insert([['id' => 1, 'shop_id' => 1], ['id' => 2, 'shop_id' => 2]]);
        $db->table('bookings')->insert([
            ['id' => 11, 'shop_id' => 1, 'service_id' => 101],
            ['id' => 12, 'shop_id' => 2, 'service_id' => 102],
        ]);
        $db->table('payments')->insert([['id' => 1, 'tag' => 'cash'], ['id' => 2, 'tag' => 'stripe']]);
        foreach ([[1, Order::class, 1], [2, Order::class, 2], [3, Booking::class, 11], [4, Booking::class, 12]] as [$id, $class, $payable]) {
            $db->table('transactions')->insert([
                'id' => $id, 'payable_type' => $class, 'payable_id' => $payable,
                'payment_sys_id' => 1, 'status' => Transaction::STATUS_PROGRESS,
            ]);
        }
        $db->table('shop_permissions')->insert([
            ['id' => 1, 'key' => 'payments.payouts.manage'],
            ['id' => 2, 'key' => 'payments.view'],
            ['id' => 3, 'key' => 'payments.refunds.manage'],
        ]);
        $db->table('shop_roles')->insert([
            ['id' => 1, 'shop_id' => 1, 'name' => 'Finance'],
            ['id' => 2, 'shop_id' => 1, 'name' => 'Read only'],
            ['id' => 3, 'shop_id' => 2, 'name' => 'Foreign finance'],
        ]);
        foreach ([[1, 1], [1, 3], [2, 2], [3, 1]] as [$role, $permission]) {
            $db->table('shop_role_permissions')->insert(['shop_role_id' => $role, 'shop_permission_id' => $permission]);
        }
        foreach ([[300, 1, Invitation::ACCEPTED], [301, 2, Invitation::ACCEPTED],
            [302, 1, Invitation::NEW], [303, 3, Invitation::ACCEPTED]] as [$user, $role, $status]) {
            $db->table('invitations')->insert(['user_id' => $user, 'shop_id' => 1, 'shop_role_id' => $role, 'status' => $status]);
        }
        $db->table('payment_process')->insert(['id' => 'isolated-intent', 'data' => '{}']);
        $db->table('order_refunds')->insert([
            ['id' => 1, 'order_id' => 1, 'status' => 'pending'],
            ['id' => 2, 'order_id' => 2, 'status' => 'pending'],
        ]);
        foreach (['wallet_histories', 'payouts', 'vendor_balances'] as $name) {
            $db->table($name)->insert(['id' => 1, 'amount' => 13.25]);
        }
    }
}