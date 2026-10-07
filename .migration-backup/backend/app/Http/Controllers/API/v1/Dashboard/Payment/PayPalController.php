<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Models\WalletHistory;
use App\Services\PaymentService\PayPalService;
use Illuminate\Http\Request;

class PayPalController extends PaymentBaseController
{
    public function __construct(private PayPalService $service)
    {
        parent::__construct($service);
    }

    /**
     * PayPal's intent=CAPTURE flow does not auto-capture on approval - a
     * separate server-side capture call is required, and only its own
     * result decides paid vs rejected. CHECKOUT.ORDER.APPROVED therefore
     * triggers that call rather than being trusted as "paid" on its own.
     * PAYMENT.CAPTURE.COMPLETED/DENIED are handled too, as a safety net
     * for a capture confirmation that arrives independently of the
     * synchronous call below (e.g. this webhook's own response to PayPal
     * was lost after the capture had already gone through) -
     * BaseService::afterHook() is idempotent (see its own
     * already-updated guard), so handling the same completion twice here
     * is safe.
     *
     * @param Request $request
     * @return array
     */
    public function paymentWebHook(Request $request): array
    {
        return $this->service->verifiedWebhook($request);
    }

}
