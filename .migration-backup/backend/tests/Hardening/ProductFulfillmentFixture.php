<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Http\Controllers\API\v1\Dashboard\User\OrderController;
use App\Http\Middleware\SanctumCheck;
use App\Models\Order;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use ReflectionProperty;

/** Only nested resource serialization is isolated; native controller/service run. */
final class CustomerFulfillmentControllerFixture extends OrderController
{
    public function successResponse(string $message = '', $data = null): JsonResponse
    {
        return new JsonResponse(['status' => true]);
    }
}

/** Shared isolated native actor/financial contract; never boot the native kernel. */
abstract class ProductFulfillmentFixture extends ProductRefundFixture
{
    protected array $referralTransactionLevels = [];

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['DB' => \Illuminate\Support\Facades\DB::class, 'Log' => \Illuminate\Support\Facades\Log::class] as $alias => $class) {
            if (!class_exists($alias)) class_alias($class, $alias);
        }
        $this->database->getConnection()->setTransactionManager(new \Illuminate\Database\DatabaseTransactionsManager());
        $this->app['config']->set('permission', [
            'models' => ['permission' => \Spatie\Permission\Models\Permission::class, 'role' => \Spatie\Permission\Models\Role::class],
            'table_names' => ['roles' => 'roles', 'model_has_roles' => 'model_has_roles', 'permissions' => 'permissions',
                'model_has_permissions' => 'model_has_permissions', 'role_has_permissions' => 'role_has_permissions'],
            'column_names' => ['model_morph_key' => 'model_id', 'role_pivot_key' => 'role_id'],
            'teams' => false, 'cache' => ['key' => 'isolated.fulfillment.permissions', 'store' => 'array'],
        ]);
        Schema::table('users', function (Blueprint $t): void {
            $t->string('firebase_token')->nullable(); $t->boolean('active')->default(true);
        });
        Schema::table('shops', fn (Blueprint $t) => $t->text('email_statuses')->nullable());
        Schema::table('orders', function (Blueprint $t): void {
            $t->boolean('current')->default(true); $t->string('canceled_note')->nullable();
        });
        $migration = require dirname(__DIR__, 2) . '/database/migrations/2026_10_06_010000_add_product_fulfillment_financial_state.php';
        $migration->up();
        Schema::table('invitations', function (Blueprint $t): void {
            $t->string('role')->default('moderator'); $t->boolean('driver_active')->default(true);
        });
        Schema::create('order_status_notes', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('order_id'); $t->string('status'); $t->text('notes')->nullable(); $t->timestamps();
        });
        Schema::create('point_histories', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('user_id'); $t->integer('model_id'); $t->string('model_type');
            $t->decimal('price'); $t->string('note')->nullable(); $t->timestamps();
        });
        Schema::create('roles', function (Blueprint $t): void {
            $t->increments('id'); $t->string('name'); $t->string('guard_name');
        });
        Schema::create('model_has_roles', function (Blueprint $t): void {
            $t->integer('role_id'); $t->integer('model_id'); $t->string('model_type');
        });
        Schema::create('points', function (Blueprint $t): void {
            $t->increments('id'); $t->boolean('active'); $t->integer('value');
            $t->string('for'); $t->string('type'); $t->decimal('price');
        });
        $db = $this->database;
        $db->table('roles')->insert([
            ['id' => 1, 'name' => 'deliveryman', 'guard_name' => 'sanctum'],
            ['id' => 2, 'name' => 'admin', 'guard_name' => 'sanctum'],
        ]);
        foreach ([1, 2] as $role) $db->table('model_has_roles')->insert([
            'role_id' => $role, 'model_id' => 3, 'model_type' => \App\Models\User::class,
        ]);
        $db->table('invitations')->insert(['user_id' => 3, 'shop_id' => 1,
            'shop_role_id' => 1, 'role' => 'deliveryman', 'status' => \App\Models\Invitation::ACCEPTED]);
        $db->table('shop_permissions')->insert(['id' => 3, 'key' => 'orders.manage']);
        $db->table('shop_role_permissions')->insert(['shop_role_id' => 1, 'shop_permission_id' => 3]);
        // Explicit synthetic eligible Order only; all other migrated fixtures stay unverified.
        $db->table('orders')->where('id', 1)->update([
            'delivery_type' => Order::DELIVERY, 'fulfillment_financial_state' => Order::FULFILLMENT_PENDING,
        ]);
        $db->table('orders')->insert([
            ['id' => 3, 'shop_id' => 2, 'user_id' => 3, 'deliveryman_id' => 3],
            ['id' => 4, 'shop_id' => 1, 'user_id' => 3, 'deliveryman_id' => 3],
        ]);
        $db->table('payments')->insert(['id' => 2, 'tag' => 'cash', 'active' => 1]);
        $db->table('transactions')->update(['status' => 'progress', 'payment_sys_id' => 2]);
        $db->table('platform_fee_ledger_entries')->delete();
        $db->table('order_refunds')->delete();
        $bus = \Mockery::mock(\Illuminate\Contracts\Bus\Dispatcher::class);
        $bus->shouldReceive('dispatchAfterResponse')->andReturnUsing(function ($job): void {
            if ($job instanceof \App\Jobs\PayReferral) {
                ++$this->referralJobs;
                $this->referralTransactionLevels[] = $this->database->getConnection()->transactionLevel();
            }
        });
        $bus->shouldReceive('dispatch')->andReturn(null);
        $this->app->instance(\Illuminate\Contracts\Bus\Dispatcher::class, $bus);
        $controller = (new ReflectionClass(CustomerFulfillmentControllerFixture::class))->newInstanceWithoutConstructor();
        (new ReflectionProperty(OrderController::class, 'language'))->setValue($controller, 'en');
        $this->app->instance(CustomerFulfillmentControllerFixture::class, $controller);
        $this->app['router']->post('/fixture/customer/orders/{id}/status/change',
            [CustomerFulfillmentControllerFixture::class, 'orderStatusChange'])->middleware(SanctumCheck::class);
        $this->authenticate(2);
    }
}