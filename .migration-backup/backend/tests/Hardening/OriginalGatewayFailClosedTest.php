<?php
declare(strict_types=1);

namespace Tests\Hardening;

use App\Http\Controllers\API\v1\Dashboard\Payment\IyzicoController;
use App\Http\Controllers\API\v1\Dashboard\Payment\MaksekeskusController;
use App\Http\Controllers\API\v1\Dashboard\Payment\MercadoPagoController;
use App\Http\Controllers\API\v1\Dashboard\Payment\MollieController;
use App\Http\Controllers\API\v1\Dashboard\Payment\MoyasarController;
use App\Http\Controllers\API\v1\Dashboard\Payment\PayFastController;
use App\Http\Controllers\API\v1\Dashboard\Payment\PayTabsController;
use App\Http\Controllers\API\v1\Dashboard\Payment\PayuController;
use App\Http\Controllers\API\v1\Dashboard\Payment\RazorPayController;
use App\Http\Controllers\API\v1\Dashboard\Payment\ZainCashController;
use App\Services\PaymentService\IyzicoService;
use App\Services\PaymentService\MaksekeskusService;
use App\Services\PaymentService\MercadoPagoService;
use App\Services\PaymentService\MollieService;
use App\Services\PaymentService\MoyasarService;
use App\Services\PaymentService\PayFastService;
use App\Services\PaymentService\PayTabsService;
use App\Services\PaymentService\PayuService;
use App\Services\PaymentService\RazorPayService;
use App\Services\PaymentService\ZainCashService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

final class OriginalGatewayFailClosedTest extends IsolatedTestCase
{
    private const SERVICE_CLASSES = [
        RazorPayService::class,
        PayTabsService::class,
        PayuService::class,
        MercadoPagoService::class,
        MollieService::class,
        MoyasarService::class,
        PayFastService::class,
        IyzicoService::class,
        MaksekeskusService::class,
        ZainCashService::class,
    ];

    private const CONTROLLER_CLASSES = [
        RazorPayController::class,
        PayTabsController::class,
        PayuController::class,
        MercadoPagoController::class,
        MollieController::class,
        MoyasarController::class,
        PayFastController::class,
        IyzicoController::class,
        MaksekeskusController::class,
        ZainCashController::class,
    ];

    public function testInitiationFailsBeforeAnyProviderConfigurationOrNetworkAccess(): void
    {
        foreach (self::SERVICE_CLASSES as $serviceClass) {
            $service = (new \ReflectionClass($serviceClass))->newInstanceWithoutConstructor();

            try {
                $service->processTransaction([]);
                self::fail("$serviceClass unexpectedly started a payment");
            } catch (ServiceUnavailableHttpException $exception) {
                self::assertSame(503, $exception->getStatusCode(), $serviceClass);
                self::assertSame(
                    'Payment provider temporarily unavailable',
                    $exception->getMessage(),
                    $serviceClass
                );
            }
        }
    }

    public function testCallbacksFailClosedWithTheGenericVersionedApi503Shape(): void
    {
        foreach (self::CONTROLLER_CLASSES as $controllerClass) {
            $controller = (new \ReflectionClass($controllerClass))->newInstanceWithoutConstructor();
            $response = $controller->paymentWebHook(Request::create('/api/v1/webhook/payment', 'POST', [
                'status' => 'paid',
                'amount' => '0.01',
                'token' => 'attacker-controlled',
            ]));

            self::assertSame(503, $response->getStatusCode(), $controllerClass);
            self::assertSame([
                'timestamp',
                'status',
                'statusCode',
                'message',
            ], array_keys($response->getData(true)), $controllerClass);

            $body = $response->getData(true);
            self::assertFalse($body['status'], $controllerClass);
            self::assertSame('ERROR_503', $body['statusCode'], $controllerClass);
            self::assertSame('Payment provider temporarily unavailable', $body['message'], $controllerClass);
        }
    }
}