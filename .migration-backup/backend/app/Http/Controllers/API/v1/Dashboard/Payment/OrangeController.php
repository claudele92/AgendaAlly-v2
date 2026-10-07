<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Services\PaymentService\OrangeService;
use Illuminate\Http\Request;

class OrangeController extends PaymentBaseController
{
    public function __construct(private OrangeService $service)
    {
        parent::__construct($service);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function paymentWebHook(Request $request): array
    {
        // The current Orange adapter receives no verifiable callback
        // signature and has no authoritative status-query implementation.
        // Its initiation path is disabled until those checks are available.
        return ['status' => false, 'message' => 'Payment verification failed'];
    }

}
