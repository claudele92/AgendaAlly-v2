<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use Illuminate\Http\Request;
use App\Services\PaymentService\PayuService;

class PayuController extends PaymentBaseController
{
    public function __construct(private PayuService $service)
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
