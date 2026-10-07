<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\ShopSubscriptionResource;
use App\Models\ShopSubscription;
use App\Repositories\ShopSubscriptionRepository\ShopSubscriptionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShopSubscriptionController extends AdminBaseController
{
    public function __construct(private ShopSubscriptionRepository $repository)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $shopSubscriptions = $this->repository->paginate($request->all());

        return ShopSubscriptionResource::collection($shopSubscriptions);
    }

    /**
     * Display the specified resource.
     *
     * @param ShopSubscription $shopSubscription
     * @return JsonResponse
     */
    public function show(ShopSubscription $shopSubscription): JsonResponse
    {
        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            ShopSubscriptionResource::make($this->repository->show($shopSubscription))
        );
    }
}
