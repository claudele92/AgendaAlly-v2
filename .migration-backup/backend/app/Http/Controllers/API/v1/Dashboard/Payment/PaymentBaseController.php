<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Helpers\EnvironmentPolicy;
use Throwable;
use App\Traits\ApiResponse;
use App\Services\CoreService;
use App\Models\PaymentProcess;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\PaymentService\BaseService;
use App\Http\Requests\Payment\PaymentRequest;
use App\Services\PaymentService\StripeService;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

abstract class PaymentBaseController extends Controller
{
    use ApiResponse;

    public function __construct(private BaseService|StripeService|CoreService $service)
    {
        parent::__construct();

        $this->middleware(['sanctum.check'])->except(['created', 'resultTransaction', 'mtnProcess', 'paymentWebHook']);
    }

    /**
     * process transaction.
     *
     * @param PaymentRequest $request
     * @return PaymentProcess|JsonResponse
     */
    public function processTransaction(PaymentRequest $request): PaymentProcess|JsonResponse
    {
        $provider = strtolower(str_replace(
            'Service',
            '',
            class_basename($this->service)
        ));

        if (!EnvironmentPolicy::paymentProviderEnabled($provider)) {
            return $this->errorResponse(
                'ERROR_503',
                'Payment processing is disabled for this application environment.',
                503
            );
        }

        try {
            $result = $this->service->processTransaction($request->all());

            return $this->successResponse('success', $result);
        } catch (Throwable $e) {

            if ($e instanceof ServiceUnavailableHttpException) {
                return $this->paymentProviderUnavailable();
            }

            $this->error($e);

            return $this->onErrorResponse(['message' => $e->getMessage()]);
        }
    }

    protected function paymentProviderUnavailable(): JsonResponse
    {
        return $this->errorResponse(
            'ERROR_503',
            'Payment provider temporarily unavailable',
            503
        );
    }
}
