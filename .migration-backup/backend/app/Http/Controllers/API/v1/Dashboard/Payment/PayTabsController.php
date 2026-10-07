<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Services\PaymentService\PayTabsService;
use Illuminate\Http\Request;

class PayTabsController extends PaymentBaseController
{
    public function __construct(private PayTabsService $service)
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
