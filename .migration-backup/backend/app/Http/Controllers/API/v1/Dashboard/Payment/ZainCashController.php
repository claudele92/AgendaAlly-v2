<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Services\PaymentService\ZainCashService;
use Illuminate\Http\Request;

class ZainCashController extends PaymentBaseController
{
    public function __construct(private ZainCashService $service)
    {
        parent::__construct($service);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function paymentWebHook(Request $request)
    {
        return $this->paymentProviderUnavailable();
    }

}
