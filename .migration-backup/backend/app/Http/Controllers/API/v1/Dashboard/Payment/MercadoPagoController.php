<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use Illuminate\Http\Request;
use App\Services\PaymentService\MercadoPagoService;

class MercadoPagoController extends PaymentBaseController
{
    public function __construct(private MercadoPagoService $service)
    {
        parent::__construct($service);
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
