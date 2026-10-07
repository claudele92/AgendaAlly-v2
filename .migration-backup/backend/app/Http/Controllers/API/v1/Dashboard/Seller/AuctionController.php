<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Models\Auction;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\AuctionResource;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\Auction\UpdateRequest;
use App\Services\AuctionService\AuctionService;
use App\Http\Requests\Auction\SellerStoreRequest;
use App\Repositories\AuctionRepository\AuctionRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuctionController extends SellerBaseController
{

    public function __construct(
        private AuctionService $service,
        private AuctionRepository $repository
    )
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
        $models = $this->repository->paginate($request->merge(['owner_id' => auth('sanctum')->id()])->all());

        return AuctionResource::collection($models);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param SellerStoreRequest $request
     * @return JsonResponse
     */
    public function store(SellerStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = auth('sanctum')->id();
        $validated['status']  = Auction::NEW;

        $result = $this->service->create($validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        $auction = data_get($result, 'data');

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_CREATED, locale: $this->language),
            AuctionResource::make($auction)
        );
    }

    /**
     * @param Auction $auction
     * @return JsonResponse
     */
    public function show(Auction $auction): JsonResponse
    {
        if ($auction->user_id !== auth('sanctum')->id()) {
            return $this->onErrorResponse([
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language),
                'code'    => ResponseError::ERROR_404
            ]);
        }

        $auction = $this->repository->show($auction);

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            AuctionResource::make($auction)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Auction $auction
     * @param UpdateRequest $request
     * @return JsonResponse
     */
    public function update(Auction $auction, UpdateRequest $request): JsonResponse
    {
        if ($auction->user_id !== auth('sanctum')->id()) {
            return $this->onErrorResponse([
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language),
                'code'    => ResponseError::ERROR_404
            ]);
        }

        $validated = $request->validated();
        unset($validated['status']);
        unset($validated['user_id']);

        $result = $this->service->update($auction, $validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
            AuctionResource::make($result['data'])
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function destroy(FilterParamsRequest $request): JsonResponse
    {
        $this->service->delete($request->input('ids', []), ['owner_id' => auth('sanctum')->id()]);

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_DELETED, locale: $this->language),
            []
        );
    }

}
