<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\ShopSubscriptionResource;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Repositories\ShopSubscriptionRepository\ShopSubscriptionRepository;
use App\Repositories\SubscriptionRepository\SubscriptionRepository;
use App\Services\ShopSubscriptionService\ShopSubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubscriptionController extends SellerBaseController
{
    public function __construct(
        private SubscriptionRepository $repository,
        private ShopSubscriptionRepository $shopSubscriptionRepository,
        private ShopSubscriptionService $shopSubscriptionService,
    )
    {
        parent::__construct();
    }

    /**
     * Display a listing of the available plans.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $subscriptions = $this->repository->paginate($request->merge(['active' => 1])->all());

        return SubscriptionResource::collection($subscriptions);
    }

    /**
     * Attaches this shop to a plan as a new, unpaid subscription — the step
     * before payment (see ShopSubscriptionService::attach()).
     *
     * @param Subscription $subscription
     * @return JsonResponse
     */
    public function attach(Subscription $subscription): JsonResponse
    {
        $result = $this->shopSubscriptionService->attach($this->shop->id, $subscription->id);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            ShopSubscriptionResource::make(data_get($result, 'data'))
        );
    }

    /**
     * Display this shop's own subscription history.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function mySubscriptions(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $shopSubscriptions = $this->shopSubscriptionRepository->paginate(
            $request->merge(['shop_id' => $this->shop->id])->all()
        );

        return ShopSubscriptionResource::collection($shopSubscriptions);
    }
}
