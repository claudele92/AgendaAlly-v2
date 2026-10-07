<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Repositories\PaymentRepository\PaymentRepository;
use App\Services\PaymentEligibility\PaymentContextFactory;
use App\Services\PaymentEligibility\PaymentEligibilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends RestBaseController
{
    public function __construct(private PaymentRepository $repository)
    {
        parent::__construct();
    }

    /** Owned payable/explicit business context uses the initiation resolver. */
    public function index(FilterParamsRequest $request): AnonymousResourceCollection|JsonResponse
    {
        $request->validate([
            'shop_id' => 'nullable|integer|min:1', 'location_type' => 'nullable|integer|in:1,2',
            'booking_id' => 'nullable|integer|min:1', 'cart_id' => 'nullable|integer|min:1',
            'order_id' => 'nullable|integer|min:1', 'currency_id' => 'nullable|integer|min:1',
        ]);
        $resolver = new PaymentEligibilityService;
        $context = (new PaymentContextFactory)->request($request->all());
        if ($context === null) {
            // Legacy platform-purchase catalog is not checkout authorization.
            $payments = $this->repository->paymentsList($request->merge(['active' => 1])->all())
                ->filter(fn (Payment $payment) => $resolver->catalogReady($payment))->values();
            return PaymentResource::collection($payments)->additional([
                'meta' => ['catalog_only' => true, 'checkout_authorization' => false],
            ]);
        }
        return PaymentResource::collection($resolver->methods($context))->additional([
            'meta' => ['payment_context' => $context, 'catalog_only' => false],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $payment = $this->repository->paymentDetails($id);
        if (!$payment || !(new PaymentEligibilityService)->catalogReady($payment)) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }
        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            PaymentResource::make($payment)
        );
    }
}