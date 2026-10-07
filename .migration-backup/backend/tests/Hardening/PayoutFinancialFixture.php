<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Models\Payout;
use App\Models\Wallet;
use App\Services\PayoutService\PayoutService;
use App\Services\WalletHistoryService\WalletHistoryService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait PayoutResponseFixture
{
    public function successResponse(string $message = '', $data = null): \Illuminate\Http\JsonResponse
    { return new \Illuminate\Http\JsonResponse(['status' => true]); }
}
final class PayoutAdminControllerFixture extends \App\Http\Controllers\API\v1\Dashboard\Admin\PayoutsController
{ use PayoutResponseFixture; }
final class PayoutSellerControllerFixture extends \App\Http\Controllers\API\v1\Dashboard\Seller\PayoutsController
{ use PayoutResponseFixture; }

final class PayoutHistoryFaultFixture extends WalletHistoryService
{
    public ?string $fault = null;

    public function createForPayout(array $data): array
    {
        if ($this->fault === $data['type'] . '_before') return ['status' => false];
        $result = parent::createForPayout($data);
        if ($this->fault === $data['type'] . '_after_false') return ['status' => false];
        if ($this->fault === $data['type'] . '_after_throw') throw new \RuntimeException('Injected payout history failure');
        return $result;
    }
}

abstract class PayoutFinancialFixture extends ProductFulfillmentFixture
{
    protected PayoutService $payoutService;
    protected PayoutHistoryFaultFixture $historyService;

    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('payouts', function (Blueprint $t): void {
            $t->increments('id'); $t->integer('created_by'); $t->integer('approved_by')->nullable();
            $t->integer('payment_id'); $t->integer('currency_id'); $t->decimal('price', 12, 2);
            $t->string('cause')->nullable(); $t->string('answer')->nullable();
            $t->string('status')->default('pending'); $t->timestamps();
        });
        $this->database->table('payouts')->insert([
            'id' => 1, 'created_by' => 1, 'payment_id' => 1, 'currency_id' => 1,
            'price' => 20, 'status' => 'pending',
        ]);
        $this->authenticate(3, 'admin');
        $this->payoutService = new PayoutService();
        $this->historyService = new PayoutHistoryFaultFixture();
        $this->app->instance(WalletHistoryService::class, $this->historyService);
        foreach (['admin' => PayoutAdminControllerFixture::class, 'seller' => PayoutSellerControllerFixture::class] as $role => $class) {
            $controller = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
            (new \ReflectionProperty(get_parent_class($class), 'service'))->setValue($controller, $this->payoutService);
            (new \ReflectionProperty(\App\Http\Controllers\Controller::class, 'language'))->setValue($controller, 'en');
            $this->app->instance($class, $controller);
            $this->app['router']->put("/fixture/payout/$role/{payout}", [$class, 'update'])
                ->middleware([\App\Http\Middleware\SanctumCheck::class, 'role:' . $role,
                    \Illuminate\Routing\Middleware\SubstituteBindings::class]);
        }
        $this->app['router']->post('/fixture/payout/admin/{id}/status', [PayoutAdminControllerFixture::class, 'statusChange'])
            ->middleware([\App\Http\Middleware\SanctumCheck::class, 'role:admin']);
    }

    protected function approve(): array
    {
        return $this->payoutService->statusChange(1, Payout::STATUS_ACCEPTED);
    }

    protected function payoutHttp(string $path, array $data, string $method = 'PUT'): \Symfony\Component\HttpFoundation\Response
    {
        $request = \Illuminate\Http\Request::create($path, $method, $data);
        $request->headers->set('Accept', 'application/json');
        $this->app->instance('request', $request);
        \Illuminate\Support\Facades\Facade::clearResolvedInstance('request');
        try { return $this->app['router']->dispatch($request); }
        catch (\Illuminate\Http\Exceptions\HttpResponseException $e) { return $e->getResponse(); }
        catch (\Illuminate\Validation\ValidationException $e) { return new \Illuminate\Http\JsonResponse(['errors' => $e->errors()], 422); }
        catch (\Spatie\Permission\Exceptions\UnauthorizedException $e) { return new \Illuminate\Http\JsonResponse([], 403); }
    }

    protected function payoutState(): array
    {
        $state = [];
        foreach (['payouts', 'wallets', 'wallet_histories', 'transactions', 'platform_fee_ledger_entries',
            'shops', 'orders', 'order_refunds'] as $table) {
            $state[$table] = hash('sha256', json_encode(
                $this->database->table($table)->orderBy('id')->get()->all(), JSON_THROW_ON_ERROR
            ));
        }
        return $state;
    }

    protected function assertPayoutBatch(): void
    {
        self::assertSame(60.0, (float) Wallet::where('user_id', 3)->value('price'));
        self::assertSame(120.0, (float) Wallet::where('user_id', 1)->value('price'));
        self::assertSame(180.0, (float) Wallet::whereIn('user_id', [1, 3])->sum('price'));
        self::assertSame(2, $this->database->table('wallet_histories')->count());
        self::assertSame(20.0, (float) $this->database->table('wallet_histories')->where('type', 'withdraw')->sum('price'));
        self::assertSame(20.0, (float) $this->database->table('wallet_histories')->where('type', 'topup')->sum('price'));
        self::assertSame(2, $this->database->table('transactions')->where('payable_type', \App\Models\WalletHistory::class)->where('status', 'paid')->count());
        self::assertSame(2, $this->database->table('transactions')->where('payable_type', Wallet::class)->where('status', 'progress')->count());
        self::assertSame('accepted', Payout::findOrFail(1)->status);
        self::assertSame(3, (int) Payout::findOrFail(1)->approved_by);
    }
}