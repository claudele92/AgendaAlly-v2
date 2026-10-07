<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Http\Controllers\API\v1\Dashboard\Admin\OrderRefundsController as AdminRefundController;
use App\Http\Controllers\API\v1\Dashboard\Seller\OrderRefundsController as SellerRefundController;
use App\Http\Middleware\CheckShopPermission;
use App\Http\Middleware\SanctumCheck;
use App\Models\Order;
use App\Models\OrderRefund;
use App\Models\Shop;
use App\Models\Transaction;
use App\Models\User;
use App\Services\OrderService\OrderRefundService;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Schema;
use Mockery;
use ReflectionClass;
use ReflectionProperty;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Models\Role;

/** Real native service, Wallet, observer, validation and authorization; isolated DB. */
abstract class ProductRefundFixture extends WalletTransferFixture
{
    protected OrderRefundService $refundService;
    protected int $referralJobs = 0;

    protected function setUp(): void
    {
        // Earlier isolated suites use thinner schemas. Laravel caches guarded
        // column lists independently of clearBootedModels(); don't reuse those
        // lists for the real Wallet/Transaction contract in this fixture.
        (new ReflectionProperty(\Illuminate\Database\Eloquent\Model::class, 'guardableColumns'))->setValue(null, []);
        parent::setUp();
        $this->commerceSchema();
        $db = $this->database;
        foreach ([4, 5] as $id) {
            $db->table('users')->insert(['id' => $id, 'uuid' => "user-$id",
                'firstname' => 'Synthetic', 'lastname' => 'Staff', 'lang' => 'en']);
        }
        $db->table('shops')->insert([
            ['id' => 1, 'uuid' => 'shop-1', 'user_id' => 1],
            ['id' => 2, 'uuid' => 'shop-2', 'user_id' => 3],
        ]);
        $db->table('orders')->insert([
            ['id' => 1, 'shop_id' => 1, 'user_id' => 2, 'deliveryman_id' => 3],
            ['id' => 2, 'shop_id' => 2, 'user_id' => 2, 'deliveryman_id' => 3],
        ]);
        $db->table('products')->insert(['id' => 1, 'o_count' => 1, 'od_count' => 1]);
        $db->table('stocks')->insert(['id' => 1, 'product_id' => 1, 'quantity' => 7, 'o_count' => 1, 'od_count' => 1]);
        $db->table('order_details')->insert(['id' => 1, 'order_id' => 1, 'stock_id' => 1, 'quantity' => 2, 'total_price' => 50]);
        $db->table('order_refunds')->insert(['id' => 1, 'order_id' => 1, 'status' => 'pending', 'cause' => 'Fixture']);
        $db->table('shop_roles')->insert([
            ['id' => 1, 'shop_id' => 1, 'name' => 'Finance'],
            ['id' => 2, 'shop_id' => 1, 'name' => 'View'],
        ]);
        $db->table('shop_permissions')->insert([
            ['id' => 1, 'key' => 'payments.refunds.manage'], ['id' => 2, 'key' => 'payments.view'],
        ]);
        $db->table('shop_role_permissions')->insert([
            ['shop_role_id' => 1, 'shop_permission_id' => 1], ['shop_role_id' => 2, 'shop_permission_id' => 2],
        ]);
        foreach ([4 => 1, 5 => 2] as $user => $role) {
            $db->table('invitations')->insert(['user_id' => $user, 'shop_id' => 1,
                'shop_role_id' => $role, 'status' => \App\Models\Invitation::ACCEPTED]);
        }
        $this->authenticate(1, 'seller');
        // The real paid-order observer creates the pre-existing fee entry.
        Transaction::create(['payable_type' => Order::class, 'payable_id' => 1,
            'price' => 50, 'payment_sys_id' => 1, 'user_id' => 2, 'status' => 'paid']);
        $this->refundService = new OrderRefundService();
        $bus = Mockery::mock(\Illuminate\Contracts\Bus\Dispatcher::class);
        $bus->shouldReceive('dispatchAfterResponse')->andReturnUsing(function (): void { ++$this->referralJobs; });
        $this->app->instance(\Illuminate\Contracts\Bus\Dispatcher::class, $bus);

        foreach ([SellerRefundController::class, AdminRefundController::class] as $class) {
            $controller = (new ReflectionClass($class))->newInstanceWithoutConstructor();
            (new ReflectionProperty($class, 'language'))->setValue($controller, 'en');
            (new ReflectionProperty($class, 'service'))->setValue($controller, $this->refundService);
            if ($class === SellerRefundController::class) {
                (new ReflectionProperty($class, 'shop'))->setValue($controller, Shop::findOrFail(1));
            }
            $this->app->instance($class, $controller);
        }
        $router = $this->app['router'];
        $router->aliasMiddleware('role', RoleMiddleware::class);
        $router->put('/fixture/seller/order-refunds/{orderRefund}', [SellerRefundController::class, 'update'])
            ->middleware([SanctumCheck::class, 'role:seller|moderator|shop_manager|admin',
                CheckShopPermission::class . ':payments.refunds.manage', SubstituteBindings::class]);
        $router->put('/fixture/admin/order-refunds/{orderRefund}', [AdminRefundController::class, 'update'])
            ->middleware([SanctumCheck::class, 'role:admin', SubstituteBindings::class]);
    }

    protected function tearDown(): void
    {
        (new ReflectionProperty(\Illuminate\Database\Eloquent\Model::class, 'guardableColumns'))->setValue(null, []);
        parent::tearDown();
    }

    protected function authenticate(?int $id, string $role = 'user'): void
    {
        $user = $id === null ? null : User::findOrFail($id);
        $user?->setRelation('roles', collect([(new Role())->forceFill(['name' => $role])]));
        $user?->setRelation('countryAdmin', (object) ['country_id' => null]);
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn($id);
        $guard->shouldReceive('check')->andReturn($id !== null);
        $auth = Mockery::mock(Factory::class);
        $auth->shouldReceive('guard')->andReturn($guard);
        $this->app->instance('auth', $auth);
        Facade::clearResolvedInstance('auth');
    }

    protected function updateRefund(array $data, bool $admin = false): \Symfony\Component\HttpFoundation\Response
    {
        if (!$admin) {
            $actor = auth('sanctum')->user();
            $shop = $actor?->shop ?? $actor?->moderatorShop;
            (new ReflectionProperty(SellerRefundController::class, 'shop'))
                ->setValue($this->app->make(SellerRefundController::class), $shop);
        }
        $request = Request::create('/fixture/' . ($admin ? 'admin' : 'seller') . '/order-refunds/1', 'PUT', $data);
        $request->headers->set('Accept', 'application/json');
        $this->app->instance('request', $request);
        Facade::clearResolvedInstance('request');
        try {
            return $this->app['router']->dispatch($request);
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return new \Illuminate\Http\JsonResponse(['errors' => $e->errors()], 422);
        } catch (\Spatie\Permission\Exceptions\UnauthorizedException $e) {
            return new \Illuminate\Http\JsonResponse(['message' => 'Forbidden'], 403);
        }
    }

    protected function settle(array $extra = [], ?OrderRefund $refund = null): array
    {
        return $this->refundService->update($refund ?? OrderRefund::findOrFail(1),
            array_merge($extra, ['status' => 'accepted']));
    }

    protected function persistedState(): array
    {
        $pdo = $this->database->getConnection()->getPdo();
        $result = [];
        foreach ($pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(\PDO::FETCH_COLUMN) as $table) {
            $rows = $pdo->query('SELECT * FROM "' . $table . '"')->fetchAll(\PDO::FETCH_ASSOC);
            $encoded = array_map(fn ($row) => json_encode($row, JSON_THROW_ON_ERROR), $rows);
            sort($encoded);
            $result[$table] = hash('sha256', implode("\n", $encoded));
        }
        $result['_referral_jobs'] = $this->referralJobs;
        return $result;
    }

    protected function deliveredPartners(): void
    {
        $this->database->table('orders')->where('id', 1)->update(['status' => 'delivered', 'delivery_type' => Order::DELIVERY]);
        foreach ([[1, 1, 'seller', 40], [2, 3, 'deliveryman', 5]] as [$id, $user, $type, $price]) {
            $this->database->table('payment_to_partners')->insert([
                'id' => $id, 'user_id' => $user, 'model_id' => 1, 'model_type' => Order::class, 'type' => $type,
            ]);
            Transaction::create(['payable_type' => \App\Models\PaymentToPartner::class, 'payable_id' => $id,
                'price' => $price, 'payment_sys_id' => 1, 'user_id' => $user, 'status' => 'paid']);
        }
    }

    private function commerceSchema(): void
    {
        Schema::create('shops', function (Blueprint $t): void {
            $t->increments('id'); $t->string('uuid'); $t->integer('user_id');
        });
        Schema::create('orders', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('shop_id'); $t->integer('user_id'); $t->integer('deliveryman_id');
            $t->integer('currency_id')->default(1); $t->decimal('total_price')->default(50);
            $t->decimal('service_fee')->default(5); $t->decimal('commission_fee')->default(0);
            $t->decimal('delivery_fee')->default(5); $t->decimal('coupon_price')->default(0);
            $t->decimal('tips')->default(0); $t->integer('type')->default(Order::IN_HOUSE);
            $t->string('status')->default('new'); $t->string('delivery_type')->default('pickup'); $t->timestamps();
        });
        Schema::create('order_refunds', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('order_id'); $t->string('status')->default('pending');
            $t->string('cause')->nullable(); $t->string('answer')->nullable(); $t->timestamps();
        });
        Schema::create('order_details', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('order_id'); $t->integer('stock_id');
            $t->integer('quantity'); $t->decimal('total_price'); $t->timestamps();
        });
        Schema::create('products', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('o_count'); $t->integer('od_count'); $t->timestamps(); $t->softDeletes();
        });
        Schema::create('stocks', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('product_id'); $t->integer('quantity');
            $t->integer('o_count'); $t->integer('od_count'); $t->timestamps(); $t->softDeletes();
        });
        Schema::create('digital_files', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('product_id'); $t->boolean('active')->default(true);
        });
        Schema::create('user_digital_files', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('digital_file_id')->nullable();
            $t->integer('user_id'); $t->boolean('downloaded')->default(false);
        });
        Schema::create('galleries', function (Blueprint $t): void {
            $t->increments('id'); $t->string('loadable_type'); $t->integer('loadable_id');
        });
        Schema::create('payment_to_partners', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('user_id'); $t->integer('model_id'); $t->string('model_type'); $t->string('type');
        });
        Schema::create('platform_fee_ledger_entries', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('transaction_id'); $t->string('entry_type');
            $t->string('payable_type'); $t->integer('payable_id'); $t->integer('shop_id');
            $t->integer('payment_id'); $t->integer('currency_id'); $t->decimal('amount'); $t->string('status');
            $t->timestamps(); $t->unique(['transaction_id', 'entry_type']);
        });
        Schema::create('shop_roles', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('shop_id'); $t->string('name');
        });
        Schema::create('shop_permissions', function (Blueprint $t): void {
            $t->increments('id'); $t->string('key');
        });
        Schema::create('shop_role_permissions', function (Blueprint $t): void {
            $t->integer('shop_role_id'); $t->integer('shop_permission_id');
        });
        Schema::create('invitations', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('user_id'); $t->integer('shop_id');
            $t->integer('shop_role_id'); $t->integer('status');
        });
    }
}