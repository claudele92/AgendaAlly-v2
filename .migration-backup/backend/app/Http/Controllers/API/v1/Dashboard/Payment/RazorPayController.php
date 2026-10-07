<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Models\WalletHistory;
use App\Services\PaymentService\RazorPayService;
use Illuminate\Http\Request;

class RazorPayController extends PaymentBaseController
{
    public function __construct(private RazorPayService $service)
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
