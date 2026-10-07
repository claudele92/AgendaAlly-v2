<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use Illuminate\Http\Request;
use App\Services\PaymentService\PayFastService;

class PayFastController extends PaymentBaseController
{
	public function __construct(private PayFastService $service)
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
