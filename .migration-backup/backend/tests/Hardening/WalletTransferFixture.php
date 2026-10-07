<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Http\Controllers\API\v1\Dashboard\Admin\WalletHistoryController;
use App\Http\Controllers\API\v1\Dashboard\User\WalletController;
use App\Http\Middleware\SanctumCheck;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletHistory;
use App\Observers\TransactionObserver;
use App\Services\WalletHistoryService\WalletHistoryService;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Providers\FormRequestServiceProvider;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Schema;
use Mockery;
use ReflectionClass;
use ReflectionProperty;
use Spatie\Permission\Models\Role;

/** Keep unrelated nested User/role serialization outside this financial fixture. */
final class WalletTransferControllerFixture extends WalletController
{
    public function successResponse(string $message = '', $data = null): JsonResponse
    {
        return new JsonResponse(['status' => true, 'message' => $message]);
    }
}

/** Real lifecycle; explicit fault injection only for rollback tests. */
final class WalletTransferServiceFixture extends WalletHistoryService
{
    public ?string $failAt = null;

    public function create(array $data): array
    {
        if ($this->failAt === 'recipient_before' && $data['type'] === 'topup') {
            return ['status' => false, 'code' => 'ERROR_400'];
        }
        $result = parent::create($data);
        if ($this->failAt === 'recipient_after' && $data['type'] === 'topup') {
            return ['status' => false, 'code' => 'ERROR_400'];
        }
        return $result;
    }

    public function changeStatus(string $uuid, ?string $status = null, ?int $ownerId = null): array
    {
        $result = parent::changeStatus($uuid, $status, $ownerId);
        return $this->failAt === 'finalization'
            ? ['status' => false, 'code' => 'ERROR_400'] : $result;
    }
}

abstract class WalletTransferFixture extends IsolatedTestCase
{
    protected WalletTransferServiceFixture $service;
    protected int $transactionEvents = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app['config']->set('auth.defaults.guard', 'sanctum');
        $this->app->register(\Illuminate\Filesystem\FilesystemServiceProvider::class);
        (new FormRequestServiceProvider($this->app))->boot();
        $this->schema();
        $this->database->table('currencies')->insert([
            ['id' => 1, 'title' => 'USD', 'symbol' => '$', 'rate' => 1, 'default' => 1, 'active' => 1],
            ['id' => 2, 'title' => 'XAF', 'symbol' => 'XAF', 'rate' => 2, 'default' => 0, 'active' => 1],
        ]);
        $this->database->table('languages')->insert(['locale' => 'en', 'default' => 1]);
        $this->database->table('payments')->insert(['id' => 1, 'tag' => 'wallet', 'active' => 0]);
        foreach ([1 => 100, 2 => 30, 3 => 80] as $id => $price) {
            $this->database->table('users')->insert([
                'id' => $id, 'uuid' => "user-$id", 'firstname' => "Fixture$id",
                'lastname' => 'User', 'lang' => 'en',
            ]);
            $this->database->table('wallets')->insert([
                'id' => $id, 'uuid' => "wallet-$id", 'user_id' => $id,
                'price' => $price, 'currency_id' => 1,
            ]);
        }
        $this->authenticate(1);
        Transaction::observe(TransactionObserver::class);
        Transaction::created(function (): void { ++$this->transactionEvents; });
        Transaction::updated(function (): void { ++$this->transactionEvents; });
        $this->service = new WalletTransferServiceFixture();
        $this->app->instance(WalletHistoryService::class, $this->service);
        foreach ([WalletController::class, WalletHistoryController::class] as $class) {
            // Real financial actions; unrelated constructors/presentation bypassed.
            $fixture = $class === WalletController::class ? WalletTransferControllerFixture::class : $class;
            $controller = (new ReflectionClass($fixture))->newInstanceWithoutConstructor();
            (new ReflectionProperty($controller, 'language'))->setValue($controller, 'en');
            $property = $class === WalletController::class ? 'walletHistoryService' : 'service';
            (new ReflectionProperty($class, $property))->setValue($controller, $this->service);
            if ($class === WalletController::class) {
                (new ReflectionProperty($class, 'walletHistoryRepository'))->setValue(
                    $controller, new \App\Repositories\WalletRepository\WalletHistoryRepository()
                );
            }
            $this->app->instance($class, $controller);
        }
        $router = $this->app['router'];
        foreach (['send' => 'send', 'withdraw' => 'store', 'history/{uuid}/status/change' => 'changeStatus'] as $suffix => $method) {
            $router->post("/api/v1/dashboard/user/wallet/$suffix", [WalletController::class, $method])
                ->middleware(SanctumCheck::class);
        }
        $router->get('/api/v1/dashboard/user/wallet/histories', [WalletController::class, 'walletHistories'])
            ->middleware(SanctumCheck::class);
        // Admin action binding only: native Admin route admission is not recreated.
        $router->post('/fixture/admin/wallet/history/{uuid}/status/change', [WalletHistoryController::class, 'changeStatus'])
            ->middleware(SanctumCheck::class);
    }

    protected function authenticate(?int $id, string $role = 'user'): void
    {
        $user = $id === null ? null : User::query()->findOrFail($id);
        $user?->setRelation('roles', collect([(new Role())->forceFill(['name' => $role])]));
        $user?->setRelation('countryAdmin', (object) ['country_id' => null]);
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn($id);
        $guard->shouldReceive('check')->andReturn($id !== null);
        $auth = Mockery::mock(Factory::class);
        $auth->shouldReceive('guard')->with('sanctum')->andReturn($guard);
        $this->app->instance('auth', $auth);
        Facade::clearResolvedInstance('auth');
    }

    protected function request(string $suffix, array $input = [], bool $admin = false, string $method = 'POST'): JsonResponse
    {
        $path = $admin ? "/fixture/admin/wallet/$suffix" : "/api/v1/dashboard/user/wallet/$suffix";
        $request = Request::create($path, $method, $input);
        $request->headers->set('Accept', 'application/json');
        $this->app->instance('request', $request);
        Facade::clearResolvedInstance('request');
        try {
            return $this->app['router']->dispatch($request);
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        }
    }

    protected function send(int $amount = 20, int $currency = 1, string $recipient = 'user-2'): JsonResponse
    {
        return $this->request('send', ['price' => $amount, 'currency_id' => $currency, 'uuid' => $recipient]);
    }

    protected function balances(): array
    {
        return Wallet::query()->orderBy('id')->pluck('price')->map(fn ($price) => (float) $price)->all();
    }

    protected function fingerprint(): array
    {
        $result = [];
        foreach (['wallets', 'wallet_histories', 'transactions'] as $table) {
            $result[$table] = hash('sha256', json_encode(
                $this->database->table($table)->orderBy('id')->get()->all(), JSON_THROW_ON_ERROR
            ));
        }
        return $result;
    }

    protected function withdrawal(int $userId = 1): WalletHistory
    {
        return $this->service->create([
            'type' => 'withdraw', 'price' => 20, 'user' => User::findOrFail($userId),
            'status' => WalletHistory::PROCESSED,
        ])['data'];
    }

    private function schema(): void
    {
        Schema::create('translations', function (Blueprint $t): void {
            $t->increments('id'); $t->string('locale'); $t->string('key'); $t->text('value');
        });
        Schema::create('users', function (Blueprint $t): void {
            $t->increments('id'); $t->string('uuid'); $t->string('firstname');
            $t->string('lastname'); $t->string('lang'); $t->timestamps();
        });
        Schema::create('wallets', function (Blueprint $t): void {
            $t->increments('id'); $t->string('uuid'); $t->integer('user_id');
            $t->integer('currency_id'); $t->decimal('price', 12, 2); $t->timestamps();
        });
        Schema::create('wallet_histories', function (Blueprint $t): void {
            $t->increments('id'); $t->string('uuid'); $t->string('wallet_uuid');
            $t->string('type'); $t->decimal('price', 12, 2); $t->string('note')->nullable();
            $t->integer('created_by'); $t->string('status'); $t->integer('transaction_id')->nullable();
            $t->timestamps();
        });
        Schema::create('transactions', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('parent_id')->nullable(); $t->string('payable_type');
            $t->integer('payable_id'); $t->integer('payment_sys_id')->nullable(); $t->integer('user_id')->nullable();
            $t->string('payment_trx_id')->nullable(); $t->decimal('price', 12, 2);
            $t->string('note')->nullable(); $t->dateTime('perform_time')->nullable();
            $t->string('status_description')->nullable(); $t->string('status');
            $t->dateTime('refund_time')->nullable(); $t->timestamps();
        });
        Schema::create('currencies', function (Blueprint $t): void {
            $t->increments('id'); $t->string('title'); $t->string('symbol');
            $t->decimal('rate', 12, 2); $t->boolean('default'); $t->boolean('active');
        });
        Schema::create('languages', function (Blueprint $t): void {
            $t->increments('id'); $t->string('locale'); $t->boolean('default');
        });
        Schema::create('payments', function (Blueprint $t): void {
            $t->increments('id'); $t->string('tag'); $t->boolean('active');
        });
        Schema::create('notifications', function (Blueprint $t): void {
            $t->increments('id'); $t->string('type');
        });
        Schema::create('notification_user', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('notification_id'); $t->integer('user_id'); $t->boolean('active');
        });
    }
}