<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Services\PaymentService\MaksekeskusService;
use Illuminate\Http\Request;

class MaksekeskusController extends PaymentBaseController
{
    public function __construct(private MaksekeskusService $service)
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
