<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Services\PaymentEligibility\PaymentEligibilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PaymentPolicyController extends SellerBaseController
{
    public function index(Request $request, PaymentEligibilityService $resolver): JsonResponse
    {
        $request->validate(['location_type' => 'nullable|integer|in:1,2']);
        return $this->successResponse('Payment policy', $resolver->vendorPolicy(
            $this->shop, $request->filled('location_type') ? (int) $request->input('location_type') : null
        ));
    }
}