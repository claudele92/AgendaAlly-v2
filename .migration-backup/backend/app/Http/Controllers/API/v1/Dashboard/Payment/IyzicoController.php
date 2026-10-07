<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Http\Requests\Payment\PaymentRequest;
use App\Models\PaymentProcess;
use App\Services\PaymentService\IyzicoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class IyzicoController extends PaymentBaseController
{
    public function __construct(private IyzicoService $service)
    {
        parent::__construct($service);
    }

    public function processTransaction(PaymentRequest $request): PaymentProcess|JsonResponse
    {
        try {
            return $this->service->processTransaction($request->all());
        } catch (Throwable $e) {
            if ($e instanceof ServiceUnavailableHttpException) {
                return $this->paymentProviderUnavailable();
            }

            return $this->onErrorResponse([
                'message' => $e->getMessage(),
                'code'    => (string)$e->getCode()
            ]);
        }

    }

    /**
     * @param Request $request
     * @return array
     */
    public function paymentWebHook(Request $request)
    {
        return $this->paymentProviderUnavailable();
    }

}
