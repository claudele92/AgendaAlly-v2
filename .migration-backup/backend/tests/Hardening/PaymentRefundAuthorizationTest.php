<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Http\Controllers\API\v1\Dashboard\Seller\OrderRefundsController;
use App\Http\Requests\OrderRefund\UpdateRequest;
use App\Models\OrderRefund;
use App\Models\Shop;
use App\Services\OrderService\OrderRefundService;
use Illuminate\Support\Facades\Facade;
use Mockery;
use ReflectionClass;
use ReflectionProperty;

final class PaymentRefundAuthorizationTest extends PaymentStatusFixture
{
    public function test_cross_shop_refund_and_missing_order_are_denied_before_service_or_accounting(): void
    {
        // Shop A has a real Order: the old unscoped model->where() check
        // would incorrectly use that row to authorize Shop B's refund.
        $controller = $this->controller(false);
        $before = $this->fingerprint();
        $response = $controller->update(OrderRefund::query()->findOrFail(2), $this->refundRequest());
        self::assertSame(404, $response->getStatusCode());
        self::assertSame($before, $this->fingerprint());
        $this->database->table('order_refunds')->where('id', 2)->update(['order_id' => 99999]);
        $before = $this->fingerprint();
        self::assertSame(404, $controller->update(OrderRefund::query()->findOrFail(2), $this->refundRequest())->getStatusCode());
        self::assertSame($before, $this->fingerprint());
    }

    public function test_own_shop_refund_without_grant_is_denied(): void
    {
        $this->authenticate(301);
        $controller = $this->controller(false);
        $before = $this->fingerprint();
        self::assertSame(404, $controller->update(OrderRefund::query()->findOrFail(1), $this->refundRequest())->getStatusCode());
        self::assertSame($before, $this->fingerprint());
    }

    public function test_owner_and_granted_staff_still_reach_the_unchanged_refund_service(): void
    {
        foreach ([[100, 'seller'], [300, 'seller'], [100, 'admin']] as [$actor, $role]) {
            $this->authenticate($actor, $role);
            $controller = $this->controller(true);
            self::assertSame(200, $controller->update(OrderRefund::query()->findOrFail(1), $this->refundRequest())->getStatusCode());
        }
    }

    private function controller(bool $allowed): OrderRefundsController
    {
        $controller = (new ReflectionClass(OrderRefundsController::class))->newInstanceWithoutConstructor();
        (new ReflectionProperty($controller, 'language'))->setValue($controller, 'en');
        (new ReflectionProperty($controller, 'shop'))->setValue($controller, Shop::query()->findOrFail(1));
        $service = Mockery::mock(OrderRefundService::class);
        if ($allowed) {
            $service->shouldReceive('update')->once()->withArgs(
                fn ($refund, $data) => $refund->id === 1 && $data['status'] === OrderRefund::STATUS_ACCEPTED
            )->andReturn(['status' => true]);
        } else {
            $service->shouldNotReceive('update');
        }
        (new ReflectionProperty($controller, 'service'))->setValue($controller, $service);
        return $controller;
    }

    private function refundRequest(): UpdateRequest
    {
        $request = UpdateRequest::create('/api/v1/dashboard/seller/order-refunds/2', 'PUT', [
            'status' => OrderRefund::STATUS_ACCEPTED,
            'shop_id' => 1, 'vendor_id' => 100,
        ]);
        $this->app->instance('request', $request);
        Facade::clearResolvedInstance('request');
        $request->setContainer($this->app);
        $request->setValidator($this->app['validator']->make($request->all(), $request->rules()));
        return $request;
    }
}